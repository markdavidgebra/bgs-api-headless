<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\AppointmentNote;
use App\Models\PatientSubscription;
use App\Models\TreatmentPatientPackage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PatientAftercareInstructionController extends Controller
{
    public function index(): View
    {
        $patientId = Auth::id();

        $appointmentInstructions = AppointmentNote::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.service', 'appointment.clinicalStaff'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (AppointmentNote $note) => filled($note->instructions))
            ->map(fn (AppointmentNote $note): object => $this->mapAppointmentInstruction($note));

        $treatmentInstructions = TreatmentPatientPackage::query()
            ->where('patient_id', $patientId)
            ->with('treatmentPackage')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (TreatmentPatientPackage $pkg) => filled($pkg->treatmentPackage?->aftercare))
            ->map(fn (TreatmentPatientPackage $pkg): object => $this->mapTreatmentInstruction($pkg));

        $membershipInstructions = PatientSubscription::query()
            ->where('patient_id', $patientId)
            ->with('membershipPlan')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (PatientSubscription $sub) => filled($sub->membershipPlan?->aftercare))
            ->map(fn (PatientSubscription $sub): object => $this->mapMembershipInstruction($sub));

        $instructions = (new Collection)
            ->merge($appointmentInstructions)
            ->merge($treatmentInstructions)
            ->merge($membershipInstructions)
            ->sortByDesc(fn (object $item) => $item->updated_at?->getTimestamp() ?? 0)
            ->values();

        return view('patient.aftercare.index', [
            'instructions' => $instructions,
        ]);
    }

    public function show(string $source, int $record): View
    {
        $patientId = Auth::id();

        $item = match ($source) {
            'appointment' => $this->loadAppointmentInstruction($patientId, $record),
            'treatment' => $this->loadTreatmentInstruction($patientId, $record),
            'membership' => $this->loadMembershipInstruction($patientId, $record),
            default => null,
        };

        if (! $item) {
            abort(404);
        }

        return view('patient.aftercare.show', [
            'item' => $item,
        ]);
    }

    private function loadAppointmentInstruction(int $patientId, int $record): ?object
    {
        $note = AppointmentNote::query()
            ->whereKey($record)
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.service', 'appointment.clinicalStaff'])
            ->first();

        return $note && filled($note->instructions) ? $this->mapAppointmentInstruction($note) : null;
    }

    private function loadTreatmentInstruction(int $patientId, int $record): ?object
    {
        $pkg = TreatmentPatientPackage::query()
            ->whereKey($record)
            ->where('patient_id', $patientId)
            ->with('treatmentPackage')
            ->first();

        return $pkg && filled($pkg->treatmentPackage?->aftercare) ? $this->mapTreatmentInstruction($pkg) : null;
    }

    private function loadMembershipInstruction(int $patientId, int $record): ?object
    {
        $sub = PatientSubscription::query()
            ->whereKey($record)
            ->where('patient_id', $patientId)
            ->with('membershipPlan')
            ->first();

        return $sub && filled($sub->membershipPlan?->aftercare) ? $this->mapMembershipInstruction($sub) : null;
    }

    private function mapAppointmentInstruction(AppointmentNote $note): object
    {
        $appointment = $note->appointment;

        return (object) [
            'source' => 'appointment',
            'record_id' => (int) $note->id,
            'title' => $appointment?->service_name ?: 'Appointment aftercare',
            'subtitle' => 'Appointment '.($appointment?->appointment_no ?: '#'.$note->appointment_id),
            'instructions' => (string) $note->instructions,
            'status' => (string) ($appointment?->status ?? 'pending'),
            'updated_at' => $note->updated_at ?: $note->created_at,
        ];
    }

    private function mapTreatmentInstruction(TreatmentPatientPackage $pkg): object
    {
        $treatment = $pkg->treatmentPackage;

        return (object) [
            'source' => 'treatment',
            'record_id' => (int) $pkg->id,
            'title' => $treatment?->name ?: 'Treatment package aftercare',
            'subtitle' => 'Treatment package #'.$pkg->id,
            'instructions' => (string) $treatment->aftercare,
            'status' => (string) ($pkg->status ?? 'active'),
            'updated_at' => $pkg->updated_at ?: $pkg->created_at,
        ];
    }

    private function mapMembershipInstruction(PatientSubscription $sub): object
    {
        $plan = $sub->membershipPlan;

        return (object) [
            'source' => 'membership',
            'record_id' => (int) $sub->id,
            'title' => $plan?->name ?: 'Membership aftercare',
            'subtitle' => 'Membership subscription #'.$sub->id,
            'instructions' => (string) $plan->aftercare,
            'status' => (string) ($sub->status ?? 'active'),
            'updated_at' => $sub->updated_at ?: $sub->created_at,
        ];
    }
}
