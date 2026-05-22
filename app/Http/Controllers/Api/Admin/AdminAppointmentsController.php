<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\AppointmentTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminAppointmentsController extends Controller
{
    use AdminPortalResponses;

    public function index(Request $request): JsonResponse
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

        $perPage = max(1, min((int) $request->integer('limit', 15), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Appointment $a) => $this->appointmentPayload($a))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $monthInput = (string) $request->input('month', now()->format('Y-m'));
        try {
            $monthCursor = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable) {
            $monthCursor = now()->startOfMonth();
        }

        $start = $monthCursor->copy()->startOfMonth();
        $end = $monthCursor->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->with([
                'patient:id,name',
                'service:id,name',
                'doctor:id,name',
            ])
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $byDate = $appointments
            ->groupBy(static function (Appointment $a): string {
                if (empty($a->appointment_date)) {
                    return '';
                }

                return Carbon::parse((string) $a->appointment_date)->toDateString();
            })
            ->map(fn ($group) => $group->map(fn (Appointment $a) => $this->appointmentPayload($a))->values())
            ->all();

        return response()->json([
            'month' => $monthCursor->format('Y-m'),
            'prev_month' => $monthCursor->copy()->subMonth()->format('Y-m'),
            'next_month' => $monthCursor->copy()->addMonth()->format('Y-m'),
            'appointments_by_date' => $byDate,
        ]);
    }

    public function show(int $id): JsonResponse
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

        $note = AppointmentNote::query()
            ->where('appointment_id', $appointment->id)
            ->first();

        $payment = AppointmentPayment::query()
            ->where('appointment_id', $appointment->id)
            ->orderByDesc('id')
            ->first();

        $timelines = AppointmentTimeline::query()
            ->where('appointment_id', $appointment->id)
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'appointment' => $this->appointmentPayload($appointment, true),
            'note' => $note ? $this->appointmentNotePayload($note) : null,
            'payment' => $payment ? $this->appointmentPaymentPayload($payment) : null,
            'timelines' => $timelines->map(fn ($t) => $this->appointmentTimelinePayload($t))->values(),
        ]);
    }
}
