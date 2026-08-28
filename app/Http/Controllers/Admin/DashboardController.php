<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\Slide;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $paidStatuses = ['paid', 'partial'];

        $heroSlidesCount = Slide::query()->count();

        $todayAppointments = Appointment::query()->whereDate('appointment_date', $today)->count();
        $pendingAppointments = Appointment::query()->where('status', 'pending')->count();
        $totalPatients = Patient::query()->count();
        $activeMemberships = PatientSubscription::query()->where('status', 'active')->count();

        $lowStockProducts = Product::query()
            ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert')
            ->where('stock_quantity', '>', 0)
            ->count();

        $todayRevenue = (float) Payment::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $monthlyRevenue = (float) Payment::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereBetween('payment_date', [
                now()->copy()->startOfMonth()->toDateString(),
                now()->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $weekRevenue = (float) Payment::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereBetween('payment_date', [
                now()->copy()->startOfWeek()->toDateString(),
                now()->copy()->endOfWeek()->toDateString(),
            ])
            ->sum('amount');

        $todaysSchedule = Appointment::query()
            ->with(['patient:id,name', 'doctor:id,name', 'service:id,name'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->limit(15)
            ->get();

        $unpaidPaymentsCount = Payment::query()->where('payment_status', 'unpaid')->count();

        $expiringMembershipsCount = PatientSubscription::query()
            ->where('status', 'active')
            ->whereRaw('COALESCE(end_date, renewal_date) IS NOT NULL')
            ->whereRaw('COALESCE(end_date, renewal_date) BETWEEN ? AND ?', [
                $today,
                now()->addDays(30)->toDateString(),
            ])
            ->count();

        $renewalsDueCount = PatientSubscription::query()
            ->where('status', 'active')
            ->whereNotNull('renewal_date')
            ->whereBetween('renewal_date', [
                $today,
                now()->addDays(14)->toDateString(),
            ])
            ->count();

        $cancelledReviewCount = Appointment::query()
            ->where('status', 'cancelled')
            ->where('appointment_date', '>=', now()->subDays(14)->toDateString())
            ->count();

        $revenueTrend = collect(range(6, 0))->map(function (int $d) use ($paidStatuses) {
            $day = now()->copy()->subDays($d)->startOfDay();

            return [
                'label' => $day->format('D j'),
                'value' => (float) Payment::query()
                    ->whereIn('payment_status', $paidStatuses)
                    ->whereDate('payment_date', $day->toDateString())
                    ->sum('amount'),
            ];
        });

        $appointmentStatusOrder = ['confirmed', 'pending', 'completed', 'cancelled', 'rescheduled'];
        $appointmentStatusCounts = Appointment::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $recentPayments = Payment::query()
            ->with('patient:id,name')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $lowStockItems = Product::query()
            ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get(['id', 'name', 'sku', 'stock_quantity', 'minimum_stock_alert']);

        $outOfStockItems = Product::query()
            ->where('stock_quantity', '<=', 0)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'sku']);

        $expiringMembershipsList = PatientSubscription::query()
            ->with(['patient:id,name', 'membershipPlan:id,name'])
            ->where('status', 'active')
            ->whereRaw('COALESCE(end_date, renewal_date) IS NOT NULL')
            ->whereRaw('COALESCE(end_date, renewal_date) BETWEEN ? AND ?', [
                $today,
                now()->addDays(30)->toDateString(),
            ])
            ->orderByRaw('COALESCE(end_date, renewal_date) ASC')
            ->limit(6)
            ->get();

        $newPatientsList = Patient::query()
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'name', 'email', 'created_at']);

        $newPatientIds = Patient::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('id');

        $newPatientAppointments = Appointment::query()
            ->with(['patient:id,name', 'service:id,name'])
            ->whereIn('patient_id', $newPatientIds)
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $topServices = Service::query()
            ->join('appointments', 'appointments.service_id', '=', 'services.id')
            ->select('services.name')
            ->selectRaw('COUNT(appointments.id) as booking_count')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('booking_count')
            ->limit(5)
            ->get();

        $stockMovementsRecent = StockMovement::query()
            ->with('product:id,name')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $doctorActivityToday = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->join('clinical_staff', 'clinical_staff.id', '=', 'appointments.clinical_staff_id')
            ->select('clinical_staff.id', 'clinical_staff.name')
            ->selectRaw('COUNT(appointments.id) as appointment_count')
            ->groupBy('clinical_staff.id', 'clinical_staff.name')
            ->orderByDesc('appointment_count')
            ->get();

        $topDoctorTodayName = $doctorActivityToday->first()?->name ?? '—';
        $doctorsOnDutyToday = $doctorActivityToday->count();

        $newSubscribersThisMonth = PatientSubscription::query()
            ->whereBetween('start_date', [
                now()->copy()->startOfMonth()->toDateString(),
                now()->copy()->endOfMonth()->toDateString(),
            ])
            ->count();

        $hourSlots = range(6, 20);
        $countsByHour = array_fill_keys($hourSlots, 0);
        $timesToday = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->pluck('appointment_time');
        foreach ($timesToday as $time) {
            $h = (int) Carbon::parse($time)->format('G');
            if (array_key_exists($h, $countsByHour)) {
                $countsByHour[$h]++;
            }
        }
        $todayScheduleByHour = collect($hourSlots)->map(function (int $h) use ($countsByHour) {
            return [
                'label' => Carbon::parse(sprintf('2000-01-01 %02d:00:00', $h))->format('ga'),
                'count' => $countsByHour[$h],
            ];
        })->values();

        $needsAttentionChart = collect([
            ['label' => 'Pending appts', 'value' => $pendingAppointments],
            ['label' => 'Expiring (30d)', 'value' => $expiringMembershipsCount],
            ['label' => 'Unpaid', 'value' => $unpaidPaymentsCount],
            ['label' => 'Low stock', 'value' => $lowStockProducts],
            ['label' => 'Cancelled review', 'value' => $cancelledReviewCount],
        ]);

        $membershipOverviewChart = collect([
            ['label' => 'Active', 'value' => $activeMemberships],
            ['label' => 'Expiring (30d)', 'value' => $expiringMembershipsCount],
            ['label' => 'Renewals (14d)', 'value' => $renewalsDueCount],
            ['label' => 'New this month', 'value' => $newSubscribersThisMonth],
        ]);

        $newPatientsByDay = collect(range(6, 0))->map(function (int $d) {
            $day = now()->copy()->subDays($d)->startOfDay();

            return [
                'label' => $day->format('D j'),
                'count' => Patient::query()->whereDate('created_at', $day->toDateString())->count(),
            ];
        })->values();

        $lowStockChart = $lowStockItems->map(fn ($p) => [
            'label' => Str::limit($p->name, 28),
            'qty' => (int) $p->stock_quantity,
        ])->values();

        $recentPaymentsChart = $recentPayments->reverse()->values()->map(fn ($p) => [
            'label' => ($p->payment_date?->format('M j') ?? '—').' · '.Str::limit($p->patient->name ?? '—', 12),
            'amount' => (float) $p->amount,
        ])->values();

        $doctorActivityChart = $doctorActivityToday->map(fn ($d) => [
            'label' => Str::limit($d->name, 22),
            'count' => (int) $d->appointment_count,
        ])->values();

        $topServicesChart = $topServices->map(fn ($s) => [
            'label' => Str::limit($s->name, 30),
            'count' => (int) $s->booking_count,
        ])->values();

        return view('admin.dashboard', compact(
            'heroSlidesCount',
            'todayAppointments',
            'pendingAppointments',
            'totalPatients',
            'activeMemberships',
            'lowStockProducts',
            'todayRevenue',
            'weekRevenue',
            'monthlyRevenue',
            'todaysSchedule',
            'unpaidPaymentsCount',
            'expiringMembershipsCount',
            'renewalsDueCount',
            'cancelledReviewCount',
            'revenueTrend',
            'appointmentStatusOrder',
            'appointmentStatusCounts',
            'recentPayments',
            'lowStockItems',
            'outOfStockItems',
            'expiringMembershipsList',
            'newPatientsList',
            'newPatientAppointments',
            'topServices',
            'stockMovementsRecent',
            'doctorActivityToday',
            'topDoctorTodayName',
            'doctorsOnDutyToday',
            'newSubscribersThisMonth',
            'todayScheduleByHour',
            'needsAttentionChart',
            'membershipOverviewChart',
            'newPatientsByDay',
            'lowStockChart',
            'recentPaymentsChart',
            'doctorActivityChart',
            'topServicesChart',
        ));
    }
}
