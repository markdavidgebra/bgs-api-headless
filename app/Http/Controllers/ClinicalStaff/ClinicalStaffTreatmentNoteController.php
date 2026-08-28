<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalStaffTreatmentNoteController extends Controller
{
    public function index(Request $request): View
    {
        $doctorId = auth('doctor')->id();
        $search = trim($request->string('search')->toString());
        $date = $request->string('date')->toString();

        $query = Appointment::query()
            ->with(['patient:id,name,email', 'service:id,name', 'note'])
            ->where('clinical_staff_id', $doctorId)
            ->whereHas('note')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_no', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('service', fn ($sq) => $sq->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($date !== '') {
            $query->whereDate('appointment_date', $date);
        }

        $noteRows = $query->paginate(12)->withQueryString();

        return view('clinical-staff.treatment-notes.index', [
            'noteRows' => $noteRows,
            'search' => $search,
            'date' => $date,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        abort_unless((int) $appointment->doctor_id === (int) auth('doctor')->id(), 403);

        $appointment->load(['patient:id,name,email,phone', 'service:id,name', 'note', 'prescribedProducts']);

        abort_unless($appointment->note !== null, 404);

        return view('clinical-staff.treatment-notes.show', [
            'appointment' => $appointment,
            'note' => $appointment->note,
        ]);
    }
}
