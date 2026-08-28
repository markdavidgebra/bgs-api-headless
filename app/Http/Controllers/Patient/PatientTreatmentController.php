<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\TreatmentPatientPackage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PatientTreatmentController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, ['all', 'ongoing', 'completed', 'cancelled'], true)) {
            $filter = 'all';
        }

        $packages = TreatmentPatientPackage::query()
            ->where('patient_id', Auth::id())
            ->with([
                'treatmentPackage.clinicalStaff',
                'usageHistories' => fn ($q) => $q->whereNotNull('used_on')->orderByDesc('used_on'),
            ])
            ->orderByDesc('start_date')
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->get();

        $treatments = $packages
            ->map(fn (TreatmentPatientPackage $pkg) => $this->mapPackageToTreatmentRow($pkg));

        if ($filter !== 'all') {
            $treatments = $treatments
                ->filter(fn (object $row) => $row->display_status === $filter)
                ->values();
        }

        return view('patient.treatments.index', [
            'patient' => Auth::user(),
            'treatments' => $treatments,
            'filter' => $filter,
        ]);
    }

    public function show(TreatmentPatientPackage $patientPackage): View
    {
        $this->ensureOwnsPackage($patientPackage);

        $patientPackage->load([
            'treatmentPackage.clinicalStaff',
            'treatmentPackage.services',
            'usageHistories' => fn ($q) => $q->with('service')->orderByDesc('used_on')->orderByDesc('id'),
        ]);

        [$displayStatus, $displayLabel] = $this->resolveTreatmentDisplayStatus($patientPackage);

        $total = (int) $patientPackage->total_sessions;
        $done = (int) $patientPackage->used_sessions;
        $progress = $total > 0 ? min(100, (int) round(($done / $total) * 100)) : 0;

        $started = $patientPackage->start_date ?? $patientPackage->purchased_at;
        $lastSession = $patientPackage->usageHistories->first()?->used_on;

        return view('patient.treatments.show', [
            'patient' => Auth::user(),
            'patientPackage' => $patientPackage,
            'treatment' => $patientPackage->treatmentPackage,
            'displayStatus' => $displayStatus,
            'displayLabel' => $displayLabel,
            'dateStarted' => $started ? Carbon::parse((string) $started)->format('M j, Y') : '—',
            'endDate' => $patientPackage->end_date ? Carbon::parse((string) $patientPackage->end_date)->format('M j, Y') : '—',
            'lastSessionDate' => $lastSession ? Carbon::parse((string) $lastSession)->format('M j, Y') : '—',
            'totalSessions' => $total,
            'sessionsDone' => $done,
            'progressPercent' => $progress,
        ]);
    }

    /**
     * @return object{
     *   id: int,
     *   treatment_name: string,
     *   category: string,
     *   clinical_staff_label: string,
     *   date_started: string,
     *   last_session: string,
     *   total_sessions: int,
     *   sessions_done: int,
     *   display_status: string,
     *   display_label: string
     * }
     */
    private function mapPackageToTreatmentRow(TreatmentPatientPackage $pkg): object
    {
        $treatment = $pkg->treatmentPackage;
        $doctorNames = $treatment?->clinicalStaff?->pluck('name')->filter()->unique();

        $started = $pkg->start_date ?? $pkg->purchased_at;
        $lastSession = $pkg->usageHistories->first()?->used_on;

        [$displayStatus, $displayLabel] = $this->resolveTreatmentDisplayStatus($pkg);

        return (object) [
            'id' => (int) $pkg->id,
            'treatment_name' => $treatment?->name ?? 'Treatment package',
            'category' => (string) ($treatment?->category ?? ''),
            'clinical_staff_label' => $doctorNames && $doctorNames->isNotEmpty() ? $doctorNames->implode(', ') : '—',
            'date_started' => $started ? Carbon::parse((string) $started)->format('M j, Y') : '—',
            'last_session' => $lastSession ? Carbon::parse((string) $lastSession)->format('M j, Y') : '—',
            'total_sessions' => (int) $pkg->total_sessions,
            'sessions_done' => (int) $pkg->used_sessions,
            'display_status' => $displayStatus,
            'display_label' => $displayLabel,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveTreatmentDisplayStatus(TreatmentPatientPackage $pkg): array
    {
        $rawStatus = strtolower((string) ($pkg->status ?? 'active'));
        $remaining = (int) $pkg->remaining_sessions;
        $used = (int) $pkg->used_sessions;

        if ($rawStatus === 'cancelled') {
            return ['cancelled', 'Cancelled'];
        }

        if ($rawStatus === 'completed' || $remaining <= 0) {
            return ['completed', 'Completed'];
        }

        if (in_array($rawStatus, ['active', 'pending', 'ongoing'], true) && $used > 0 && $remaining > 0) {
            return ['ongoing', 'Ongoing'];
        }

        return ['pending', 'Pending'];
    }

    private function ensureOwnsPackage(TreatmentPatientPackage $pkg): void
    {
        if ((int) $pkg->patient_id !== (int) Auth::id()) {
            abort(404);
        }
    }
}
