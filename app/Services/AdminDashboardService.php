<?php

namespace App\Services;

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

class AdminDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function payload(): array
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
            ->get()
            ->map(fn (Appointment $a) => [
                'id' => $a->id,
                'appointment_no' => $a->appointment_no,
                'appointment_date' => $a->appointment_date?->toDateString(),
                'appointment_time' => $a->appointment_time ? substr((string) $a->appointment_time, 0, 5) : null,
                'status' => $a->status,
                'patient' => $a->patient ? ['id' => $a->patient->id, 'name' => $a->patient->name] : null,
                'doctor' => $a->doctor ? ['id' => $a->doctor->id, 'name' => $a->doctor->name] : null,
                'service' => $a->service ? ['id' => $a->service->id, 'name' => $a->service->name] : null,
            ])
            ->values();

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
        })->values();

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
            ->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'payment_status' => $p->payment_status,
                'payment_date' => $p->payment_date?->toDateString(),
                'patient' => $p->patient ? ['id' => $p->patient->id, 'name' => $p->patient->name] : null,
            ])
            ->values();

        $lowStockItems = Product::query()
            ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get(['id', 'name', 'sku', 'stock_quantity', 'minimum_stock_alert'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'stock_quantity' => (int) $p->stock_quantity,
                'minimum_stock_alert' => (int) $p->minimum_stock_alert,
            ])
            ->values();

        $outOfStockItems = Product::query()
            ->where('stock_quantity', '<=', 0)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'sku'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
            ])
            ->values();

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
            ->get()
            ->map(fn (PatientSubscription $s) => [
                'id' => $s->id,
                'patient' => $s->patient ? ['id' => $s->patient->id, 'name' => $s->patient->name] : null,
                'plan' => $s->membershipPlan ? ['id' => $s->membershipPlan->id, 'name' => $s->membershipPlan->name] : null,
                'end_date' => $s->end_date?->toDateString(),
                'renewal_date' => $s->renewal_date?->toDateString(),
            ])
            ->values();

        $newPatientsList = Patient::query()
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(fn (Patient $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'created_at' => $p->created_at?->toIso8601String(),
            ])
            ->values();

        $newPatientIds = Patient::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('id');

        $newPatientAppointments = Appointment::query()
            ->with(['patient:id,name', 'service:id,name'])
            ->whereIn('patient_id', $newPatientIds)
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Appointment $a) => [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date?->toDateString(),
                'status' => $a->status,
                'patient' => $a->patient ? ['id' => $a->patient->id, 'name' => $a->patient->name] : null,
                'service' => $a->service ? ['id' => $a->service->id, 'name' => $a->service->name] : null,
            ])
            ->values();

        $topServices = Service::query()
            ->join('appointments', 'appointments.service_id', '=', 'services.id')
            ->select('services.name')
            ->selectRaw('COUNT(appointments.id) as booking_count')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('booking_count')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->name,
                'booking_count' => (int) $s->booking_count,
            ])
            ->values();

        $stockMovementsRecent = StockMovement::query()
            ->with('product:id,name')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(fn (StockMovement $m) => [
                'id' => $m->id,
                'type' => $m->type,
                'quantity' => (int) $m->quantity,
                'product' => $m->product ? ['id' => $m->product->id, 'name' => $m->product->name] : null,
                'created_at' => $m->created_at?->toIso8601String(),
            ])
            ->values();

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
        ])->values();

        $membershipOverviewChart = collect([
            ['label' => 'Active', 'value' => $activeMemberships],
            ['label' => 'Expiring (30d)', 'value' => $expiringMembershipsCount],
            ['label' => 'Renewals (14d)', 'value' => $renewalsDueCount],
            ['label' => 'New this month', 'value' => $newSubscribersThisMonth],
        ])->values();

        $newPatientsByDay = collect(range(6, 0))->map(function (int $d) {
            $day = now()->copy()->subDays($d)->startOfDay();

            return [
                'label' => $day->format('D j'),
                'count' => Patient::query()->whereDate('created_at', $day->toDateString())->count(),
            ];
        })->values();

        $lowStockChart = $lowStockItems->map(fn ($p) => [
            'label' => Str::limit($p['name'], 28),
            'qty' => $p['stock_quantity'],
        ])->values();

        $recentPaymentsChart = $recentPayments->reverse()->values()->map(fn ($p) => [
            'label' => ($p['payment_date'] ?? '—').' · '.Str::limit($p['patient']['name'] ?? '—', 12),
            'amount' => $p['amount'],
        ])->values();

        $doctorActivityChart = $doctorActivityToday->map(fn ($d) => [
            'label' => Str::limit($d->name, 22),
            'count' => (int) $d->appointment_count,
        ])->values();

        $topServicesChart = $topServices->map(fn ($s) => [
            'label' => Str::limit($s['name'], 30),
            'count' => $s['booking_count'],
        ])->values();

        return [
            'stats' => [
                'hero_slides_count' => $heroSlidesCount,
                'today_appointments' => $todayAppointments,
                'pending_appointments' => $pendingAppointments,
                'total_patients' => $totalPatients,
                'active_memberships' => $activeMemberships,
                'low_stock_products' => $lowStockProducts,
                'today_revenue' => $todayRevenue,
                'week_revenue' => $weekRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'unpaid_payments_count' => $unpaidPaymentsCount,
                'expiring_memberships_count' => $expiringMembershipsCount,
                'renewals_due_count' => $renewalsDueCount,
                'cancelled_review_count' => $cancelledReviewCount,
                'top_doctor_today_name' => $topDoctorTodayName,
                'doctors_on_duty_today' => $doctorsOnDutyToday,
                'new_subscribers_this_month' => $newSubscribersThisMonth,
            ],
            'todays_schedule' => $todaysSchedule,
            'revenue_trend' => $revenueTrend,
            'appointment_status_order' => $appointmentStatusOrder,
            'appointment_status_counts' => $appointmentStatusCounts,
            'recent_payments' => $recentPayments,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'expiring_memberships_list' => $expiringMembershipsList,
            'new_patients_list' => $newPatientsList,
            'new_patient_appointments' => $newPatientAppointments,
            'top_services' => $topServices,
            'stock_movements_recent' => $stockMovementsRecent,
            'doctor_activity_today' => $doctorActivityToday->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'appointment_count' => (int) $d->appointment_count,
            ])->values(),
            'today_schedule_by_hour' => $todayScheduleByHour,
            'needs_attention_chart' => $needsAttentionChart,
            'membership_overview_chart' => $membershipOverviewChart,
            'new_patients_by_day' => $newPatientsByDay,
            'low_stock_chart' => $lowStockChart,
            'recent_payments_chart' => $recentPaymentsChart,
            'doctor_activity_chart' => $doctorActivityChart,
            'top_services_chart' => $topServicesChart,
        ];
    }
}
