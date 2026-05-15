<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Service;
use App\Models\TreatmentPatientPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        $paidStatuses = ['paid', 'partial'];

        $stats = [
            'total_revenue' => (float) Payment::query()->whereIn('payment_status', $paidStatuses)->sum('amount'),
            'total_appointments' => Appointment::query()->count(),
            'total_patients' => Patient::query()->count(),
            'active_subscriptions' => PatientSubscription::query()->where('status', 'active')->count(),
            'total_packages_sold' => TreatmentPatientPackage::query()->count(),
        ];

        $monthlyRevenue = collect(range(5, 0))->map(function (int $monthsAgo) use ($paidStatuses) {
            $month = now()->copy()->subMonths($monthsAgo)->startOfMonth();
            $value = (float) Payment::query()
                ->whereIn('payment_status', $paidStatuses)
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');

            return [
                'month' => $month->format('M'),
                'value' => $value,
            ];
        })->all();

        $maxRevenue = max(array_column($monthlyRevenue, 'value')) ?: 1.0;

        $appointmentTotal = Appointment::query()->count();
        $appointmentStatus = Appointment::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->orderByDesc('c')
            ->get()
            ->map(function ($row) use ($appointmentTotal) {
                $count = (int) $row->c;
                $pct = $appointmentTotal > 0 ? (int) round(($count / $appointmentTotal) * 100) : 0;

                return [
                    'label' => ucfirst(str_replace('_', ' ', (string) ($row->status ?? 'unknown'))),
                    'value' => $pct,
                    'count' => $count,
                    'color' => match ($row->status) {
                        'completed' => 'bg-green',
                        'pending' => 'bg-yellow',
                        'confirmed' => 'bg-azure',
                        'cancelled' => 'bg-red',
                        'rescheduled' => 'bg-secondary',
                        default => 'bg-secondary',
                    },
                ];
            })
            ->all();

        if ($appointmentTotal === 0) {
            $appointmentStatus = [
                ['label' => 'No appointments yet', 'value' => 0, 'count' => 0, 'color' => 'bg-secondary'],
            ];
        }

        $topServices = Service::query()
            ->join('appointments', 'appointments.service_id', '=', 'services.id')
            ->select('services.id', 'services.name')
            ->selectRaw('COUNT(appointments.id) as appointment_count')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('appointment_count')
            ->limit(5)
            ->get();

        $recentPayments = Payment::query()
            ->with(['patient:id,name'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentAppointments = Appointment::query()
            ->with(['patient:id,name', 'service:id,name'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $expiringSubscriptions = PatientSubscription::query()
            ->with(['patient:id,name', 'membershipPlan:id,name'])
            ->where('status', 'active')
            ->whereRaw('COALESCE(end_date, renewal_date) IS NOT NULL')
            ->whereRaw('COALESCE(end_date, renewal_date) BETWEEN ? AND ?', [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ])
            ->orderByRaw('COALESCE(end_date, renewal_date) ASC')
            ->limit(10)
            ->get()
            ->map(function (PatientSubscription $sub) {
                $expiry = $sub->end_date ?? $sub->renewal_date;
                $daysLeft = $expiry
                    ? today()->startOfDay()->diffInDays($expiry->copy()->startOfDay())
                    : 0;

                return [
                    'patient' => $sub->patient?->name ?? '—',
                    'plan' => $sub->membershipPlan?->name ?? '—',
                    'expires_on' => $expiry?->format('Y-m-d'),
                    'days_left' => $daysLeft,
                ];
            })
            ->all();

        return view('admin.reports.index', compact(
            'stats',
            'monthlyRevenue',
            'maxRevenue',
            'appointmentStatus',
            'topServices',
            'recentPayments',
            'recentAppointments',
            'expiringSubscriptions',
        ));
    }

    public function revenue(Request $request): View
    {
        $paidStatuses = ['paid', 'partial'];

        $base = $this->revenuePaymentsBaseQuery($request);

        $totalRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)->sum('amount');

        $nowPh = now('Asia/Manila');
        $today = $nowPh->toDateString();
        $dailyRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $weeklyRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->whereBetween('payment_date', [
                $nowPh->copy()->startOfWeek()->toDateString(),
                $nowPh->copy()->endOfWeek()->toDateString(),
            ])
            ->sum('amount');

        $monthlyRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->whereBetween('payment_date', [
                $nowPh->copy()->startOfMonth()->toDateString(),
                $nowPh->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $serviceRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->whereIn('reference_type', ['appointment', 'service'])
            ->sum('amount');

        $packageRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->where('reference_type', 'package')
            ->sum('amount');

        $subscriptionRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->where('reference_type', 'membership')
            ->sum('amount');

        $productRevenue = (float) (clone $base)->whereIn('payment_status', $paidStatuses)
            ->where('reference_type', 'product')
            ->sum('amount');

        $topMethodRow = (clone $base)->whereIn('payment_status', $paidStatuses)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->first();

        $topMethodLabel = '—';
        if ($topMethodRow && $topMethodRow->payment_method) {
            $topMethodLabel = (new Payment(['payment_method' => $topMethodRow->payment_method]))->method_label;
        }

        $rowCount = (clone $base)->count();

        $payments = (clone $base)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $methodOptions = [
            'cash' => 'Cash',
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'card' => 'Card',
            'bank_transfer' => 'Bank transfer',
        ];

        $typeOptions = [
            'appointment' => 'Appointment',
            'service' => 'Service',
            'package' => 'Package',
            'membership' => 'Membership',
            'product' => 'Product',
        ];

        return view('admin.reports.revenue', compact(
            'totalRevenue',
            'dailyRevenue',
            'weeklyRevenue',
            'monthlyRevenue',
            'serviceRevenue',
            'packageRevenue',
            'subscriptionRevenue',
            'productRevenue',
            'topMethodLabel',
            'rowCount',
            'payments',
            'methodOptions',
            'typeOptions',
        ));
    }

    /**
     * Payments query honoring revenue report filters (date range, method, reference type).
     */
    private function revenuePaymentsBaseQuery(Request $request): Builder
    {
        $query = Payment::query()->with(['patient:id,name']);

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->date('to'));
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->string('method')->toString());
        }

        if ($request->filled('type')) {
            $query->where('reference_type', $request->string('type')->toString());
        }

        return $query;
    }

    public function appointments(Request $request): View
    {
        $base = $this->appointmentsReportBaseQuery($request);

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (clone $base)->count();
        $completed = (int) ($statusCounts['completed'] ?? 0);
        $cancelled = (int) ($statusCounts['cancelled'] ?? 0);
        $pending = (int) ($statusCounts['pending'] ?? 0);
        $confirmed = (int) ($statusCounts['confirmed'] ?? 0);
        $rescheduled = (int) ($statusCounts['rescheduled'] ?? 0);

        $byDate = $this->appointmentsCountByDay($base);
        $byStatus = [
            ['label' => 'Completed', 'class' => 'bg-green', 'value' => $completed],
            ['label' => 'Cancelled', 'class' => 'bg-red', 'value' => $cancelled],
            ['label' => 'Pending', 'class' => 'bg-yellow', 'value' => $pending],
            ['label' => 'Confirmed', 'class' => 'bg-azure', 'value' => $confirmed],
            ['label' => 'Rescheduled', 'class' => 'bg-cyan', 'value' => $rescheduled],
        ];

        $byDoctor = (clone $base)
            ->join('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->select('doctors.id', 'doctors.name')
            ->selectRaw('COUNT(appointments.id) as appointment_count')
            ->groupBy('doctors.id', 'doctors.name')
            ->orderByDesc('appointment_count')
            ->limit(15)
            ->get();

        $appointments = (clone $base)
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $doctors = Doctor::query()->orderBy('name')->get(['id', 'name']);
        $services = Service::query()->orderBy('name')->get(['id', 'name']);

        $statusOptions = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rescheduled' => 'Rescheduled',
        ];

        return view('admin.reports.appointments', compact(
            'total',
            'completed',
            'cancelled',
            'pending',
            'confirmed',
            'rescheduled',
            'byDate',
            'byStatus',
            'byDoctor',
            'appointments',
            'doctors',
            'services',
            'statusOptions',
        ));
    }

    private function appointmentsReportBaseQuery(Request $request): Builder
    {
        $query = Appointment::query()
            ->with(['patient:id,name', 'doctor:id,name', 'service:id,name']);

        if ($request->filled('from')) {
            $query->whereDate('appointment_date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('appointment_date', '<=', $request->date('to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', (int) $request->input('doctor_id'));
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->input('service_id'));
        }

        return $query;
    }

    private function appointmentsCountByDay(Builder $appointmentsQuery): Collection
    {
        $driver = $appointmentsQuery->getConnection()->getDriverName();
        $expr = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', appointment_date)",
            default => 'DATE(appointment_date)',
        };

        return (clone $appointmentsQuery)
            ->selectRaw("{$expr} as day")
            ->selectRaw('COUNT(*) as c')
            ->groupByRaw($expr)
            ->orderByDesc('day')
            ->limit(45)
            ->get();
    }

    public function services(Request $request): View
    {
        $apptFiltered = $this->appointmentDateRangeQuery($request);

        $bookingCounts = (clone $apptFiltered)
            ->whereNotNull('service_id')
            ->selectRaw('service_id, COUNT(*) as c')
            ->groupBy('service_id')
            ->pluck('c', 'service_id');

        $cancelledCounts = (clone $apptFiltered)
            ->where('status', 'cancelled')
            ->whereNotNull('service_id')
            ->selectRaw('service_id, COUNT(*) as c')
            ->groupBy('service_id')
            ->pluck('c', 'service_id');

        $paidStatuses = ['paid', 'partial'];
        $revenueQuery = Payment::query()
            ->where('reference_type', 'appointment')
            ->whereIn('payment_status', $paidStatuses)
            ->join('appointments', 'appointments.id', '=', 'payments.reference_id');

        if ($request->filled('from')) {
            $revenueQuery->whereDate('appointments.appointment_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $revenueQuery->whereDate('appointments.appointment_date', '<=', $request->date('to'));
        }

        $revenueByService = $revenueQuery
            ->selectRaw('appointments.service_id, SUM(payments.amount) as total')
            ->groupBy('appointments.service_id')
            ->pluck('total', 'service_id');

        $serviceModels = Service::query()
            ->when($request->filled('catalog_status'), function ($q) use ($request) {
                $q->where('status', $request->string('catalog_status')->toString());
            })
            ->orderBy('name')
            ->get();

        $serviceRows = $serviceModels->map(function (Service $s) use ($bookingCounts, $cancelledCounts, $revenueByService) {
            return [
                'service' => $s,
                'name' => $s->name,
                'bookings' => (int) ($bookingCounts[$s->id] ?? 0),
                'revenue' => (float) ($revenueByService[$s->id] ?? 0),
                'cancellations' => (int) ($cancelledCounts[$s->id] ?? 0),
                'duration_minutes' => $s->duration_minutes,
            ];
        });

        $col = collect($serviceRows->all());
        $withBookings = $col->filter(fn (array $r) => $r['bookings'] > 0);

        $mostBookedName = $withBookings->sortByDesc('bookings')->first()['name'] ?? '—';
        $leastBookedName = $withBookings->sortBy('bookings')->first()['name'] ?? '—';

        $highestCancellationRow = $col->filter(fn (array $r) => $r['cancellations'] > 0)->sortByDesc('cancellations')->first();
        $highestCancellationName = $highestCancellationRow['name'] ?? '—';

        $avgRevenue = $col->isNotEmpty() ? (float) $col->avg('revenue') : 0.0;

        $topByBookings = $col->sortByDesc('bookings')->take(12)->values();
        $topByRevenue = $col->sortByDesc('revenue')->take(12)->values();
        $maxBookings = max(1, (int) $topByBookings->max('bookings'));
        $maxRevenue = max(1.0, (float) $topByRevenue->max('revenue'));

        $catalogStatusOptions = [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];

        return view('admin.reports.services', compact(
            'serviceRows',
            'mostBookedName',
            'leastBookedName',
            'highestCancellationName',
            'avgRevenue',
            'topByBookings',
            'topByRevenue',
            'maxBookings',
            'maxRevenue',
            'catalogStatusOptions',
        ));
    }

    private function appointmentDateRangeQuery(Request $request): Builder
    {
        $query = Appointment::query();

        if ($request->filled('from')) {
            $query->whereDate('appointment_date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('appointment_date', '<=', $request->date('to'));
        }

        return $query;
    }

    public function patients(Request $request): View
    {
        $paidStatuses = ['paid', 'partial'];

        $patientBaseQuery = Patient::query();

        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim()->toString().'%';
            $patientBaseQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        if ($request->filled('account_status')) {
            $patientBaseQuery->where('status', $request->string('account_status')->toString());
        }

        $patientBaseQuery->orderBy('name');

        $appointmentCounts = Appointment::query()
            ->selectRaw('patient_id, COUNT(*) as c')
            ->groupBy('patient_id')
            ->pluck('c', 'patient_id');

        $apptRange = Appointment::query();
        if ($request->filled('from')) {
            $apptRange->whereDate('appointment_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $apptRange->whereDate('appointment_date', '<=', $request->date('to'));
        }

        $visitCountsInRange = (clone $apptRange)
            ->selectRaw('patient_id, COUNT(*) as c')
            ->groupBy('patient_id')
            ->pluck('c', 'patient_id');

        $paymentRange = Payment::query()->whereIn('payment_status', $paidStatuses);
        if ($request->filled('from')) {
            $paymentRange->whereDate('payment_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $paymentRange->whereDate('payment_date', '<=', $request->date('to'));
        }

        $spentInRange = (clone $paymentRange)
            ->selectRaw('patient_id, SUM(amount) as total')
            ->groupBy('patient_id')
            ->pluck('total', 'patient_id');

        $spentAllTime = Payment::query()
            ->whereIn('payment_status', $paidStatuses)
            ->selectRaw('patient_id, SUM(amount) as total')
            ->groupBy('patient_id')
            ->pluck('total', 'patient_id');

        $hasActivityRange = $request->filled('from') || $request->filled('to');
        $visitCountsForTable = $hasActivityRange ? $visitCountsInRange : $appointmentCounts;
        $spentForTable = $hasActivityRange ? $spentInRange : $spentAllTime;
        $spenderSource = $hasActivityRange ? $spentInRange : $spentAllTime;

        $lastVisitByPatient = Appointment::query()
            ->selectRaw('patient_id, MAX(appointment_date) as last_dt')
            ->groupBy('patient_id')
            ->pluck('last_dt', 'patient_id');

        $activeSubPatientIds = array_flip(PatientSubscription::query()
            ->where('status', 'active')
            ->pluck('patient_id')
            ->all());

        $forKpis = (clone $patientBaseQuery)->get(['id', 'status']);
        $totalPatients = $forKpis->count();
        $newPatients = 0;
        $returningPatients = 0;
        $inactivePatients = 0;

        foreach ($forKpis as $p) {
            $status = $p->status ?? 'active';
            if ($status === 'inactive') {
                $inactivePatients++;

                continue;
            }

            $ac = (int) ($appointmentCounts[$p->id] ?? 0);
            if ($ac >= 2) {
                $returningPatients++;
            } else {
                $newPatients++;
            }
        }

        $topSpendingName = '—';
        if ($spenderSource->isNotEmpty()) {
            $topId = $spenderSource->sortDesc()->keys()->first();
            if ($topId) {
                $topSpendingName = Patient::query()->whereKey($topId)->value('name') ?? '—';
            }
        }

        $newPerMonth = collect(range(5, 0))->map(function (int $monthsAgo) {
            $m = now()->copy()->subMonths($monthsAgo)->startOfMonth();
            $count = Patient::query()
                ->whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->count();

            return [
                'month' => $m->format('M'),
                'count' => $count,
            ];
        })->all();

        $maxNew = max(1, max(array_column($newPerMonth, 'count')));

        $activeEngaged = $newPatients + $returningPatients;
        $returningPct = $activeEngaged > 0 ? (int) round(($returningPatients / $activeEngaged) * 100) : 0;
        $newPct = max(0, 100 - $returningPct);

        $patients = $patientBaseQuery->paginate(20)->withQueryString();

        $patients->setCollection(
            $patients->getCollection()->map(function (Patient $p) use ($visitCountsForTable, $spentForTable, $lastVisitByPatient, $activeSubPatientIds) {
                return [
                    'patient' => $p,
                    'visits' => (int) ($visitCountsForTable[$p->id] ?? 0),
                    'spent' => (float) ($spentForTable[$p->id] ?? 0),
                    'last_visit' => $lastVisitByPatient[$p->id] ?? null,
                    'membership_active' => isset($activeSubPatientIds[$p->id]),
                ];
            })
        );

        $accountStatusOptions = [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];

        return view('admin.reports.patients', compact(
            'totalPatients',
            'newPatients',
            'returningPatients',
            'inactivePatients',
            'topSpendingName',
            'newPerMonth',
            'maxNew',
            'returningPct',
            'newPct',
            'patients',
            'accountStatusOptions',
            'hasActivityRange',
        ));
    }

    public function subscriptions(Request $request): View
    {
        $paidStatuses = ['paid', 'partial'];

        $base = PatientSubscription::query();

        if ($request->filled('status')) {
            $base->where('status', $request->string('status')->toString());
        }

        if ($request->filled('plan_id')) {
            $base->where('membership_plan_id', (int) $request->input('plan_id'));
        }

        if ($request->filled('from')) {
            $base->whereDate('start_date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $base->whereDate('start_date', '<=', $request->date('to'));
        }

        $active = (clone $base)->where('status', 'active')->count();
        $expired = (clone $base)->where('status', 'expired')->count();
        $cancelled = (clone $base)->where('status', 'cancelled')->count();
        $paused = (clone $base)->where('status', 'paused')->count();

        $startOfMonth = now()->copy()->startOfMonth();
        $endOfMonth = now()->copy()->endOfMonth();
        $newThisMonth = (clone $base)
            ->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->count();

        $membershipRevenueTotal = (float) Payment::query()
            ->where('reference_type', 'membership')
            ->whereIn('payment_status', $paidStatuses)
            ->sum('amount');

        $notActive = $expired + $cancelled + $paused;
        $totalForBar = max(1, $active + $notActive);
        $activePct = (int) round(($active / $totalForBar) * 100);
        $notActivePct = max(0, 100 - $activePct);

        $revenueByMonth = collect(range(5, 0))->map(function (int $monthsAgo) use ($paidStatuses) {
            $m = now()->copy()->subMonths($monthsAgo)->startOfMonth();

            $value = (float) Payment::query()
                ->where('reference_type', 'membership')
                ->whereIn('payment_status', $paidStatuses)
                ->whereYear('payment_date', $m->year)
                ->whereMonth('payment_date', $m->month)
                ->sum('amount');

            return [
                'month' => $m->format('M'),
                'value' => $value,
            ];
        })->all();

        $maxRevMonth = max(1.0, max(array_column($revenueByMonth, 'value')));

        $plans = MembershipPlan::query()->orderBy('name')->get(['id', 'name']);

        $statusOptions = [
            'active' => 'Active',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'paused' => 'Paused',
        ];

        $subscriptions = (clone $base)
            ->with(['patient:id,name,email', 'membershipPlan:id,name'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.subscriptions', compact(
            'active',
            'expired',
            'cancelled',
            'paused',
            'newThisMonth',
            'membershipRevenueTotal',
            'activePct',
            'notActivePct',
            'revenueByMonth',
            'maxRevMonth',
            'subscriptions',
            'plans',
            'statusOptions',
        ));
    }
}
