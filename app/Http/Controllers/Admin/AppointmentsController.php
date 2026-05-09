<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\AppointmentTimeline;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::query()
            ->with([
                'patient:id,name,email',
                'doctor:id,name',
                'service:id,name',
            ])
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('appointment_no', 'like', '%'.$term.'%')
                    ->orWhereHas('patient', function ($q) use ($term) {
                        $q->where('name', 'like', '%'.$term.'%')
                            ->orWhere('email', 'like', '%'.$term.'%');
                    })
                    ->orWhereHas('doctor', fn ($q) => $q->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('service', fn ($q) => $q->where('name', 'like', '%'.$term.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('admin.appointment.index', compact('appointments'));
    }

    public function show(int $id): View
    {
        $appointment = Appointment::query()
            ->with([
                'patient',
                'doctor',
                'service',
                'createdByAdmin:id,name',
                'updatedByAdmin:id,name',
            ])
            ->findOrFail($id);

        $appointmentNote = AppointmentNote::query()
            ->where('appointment_id', $appointment->id)
            ->select([
                'id',
                'appointment_id',
                'patient_concern',
                'appointment_remarks',
                'admin_notes',
                'doctor_notes',
                'instructions',
                'alerts',
            ])
            ->first();

        $appointmentPayment = AppointmentPayment::query()
            ->where('appointment_id', $appointment->id)
            ->select([
                'id',
                'appointment_id',
                'invoice_no',
                'amount',
                'payment_method',
                'payment_status',
                'is_paid',
                'deposit_notes',
                'reference_no',
                'paid_at',
            ])
            ->orderByDesc('id')
            ->first();

        $appointmentTimelines = AppointmentTimeline::query()
            ->where('appointment_id', $appointment->id)
            ->select([
                'id',
                'appointment_id',
                'event',
                'description',
                'event_at',
            ])
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.appointment.show', compact(
            'appointment',
            'appointmentNote',
            'appointmentPayment',
            'appointmentTimelines',
        ));
    }
}
