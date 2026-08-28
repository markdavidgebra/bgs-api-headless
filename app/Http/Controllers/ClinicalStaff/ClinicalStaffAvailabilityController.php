<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\ClinicalStaffBlockedDate;
use App\Models\ClinicalStaffWeeklySchedule;
use App\Support\AppointmentBookingRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalStaffAvailabilityController extends Controller
{
    public function index(): View
    {
        $doctor = auth('clinical_staff')->user();
        $this->ensureDefaultWeeklySchedule($doctor->id);

        $weeklySchedules = ClinicalStaffWeeklySchedule::query()
            ->where('clinical_staff_id', $doctor->id)
            ->orderBy('weekday')
            ->get();

        $blockedDates = ClinicalStaffBlockedDate::query()
            ->where('clinical_staff_id', $doctor->id)
            ->orderBy('blocked_date')
            ->get();

        return view('clinical-staff.availability.index', compact('weeklySchedules', 'blockedDates'));
    }

    public function editWeekday(int $weekday): View
    {
        $doctorId = auth('clinical_staff')->id();
        abort_unless($weekday >= 1 && $weekday <= 7, 404);
        abort_if($weekday === AppointmentBookingRules::CLOSED_WEEKDAY, 404);

        $this->ensureDefaultWeeklySchedule($doctorId);

        $schedule = ClinicalStaffWeeklySchedule::query()
            ->where('clinical_staff_id', $doctorId)
            ->where('weekday', $weekday)
            ->firstOrFail();

        return view('clinical-staff.availability.edit', compact('schedule'));
    }

    public function updateWeekday(Request $request, int $weekday): RedirectResponse
    {
        $doctorId = auth('clinical_staff')->id();
        abort_unless($weekday >= 1 && $weekday <= 7, 404);

        if ($weekday === AppointmentBookingRules::CLOSED_WEEKDAY && $request->boolean('is_active')) {
            return redirect()
                ->route('clinical_staff.availability')
                ->with('info', 'Sunday is closed for bookings and cannot be enabled.');
        }

        $isActive = $request->boolean('is_active');

        $rules = [
            'is_active' => ['sometimes', 'boolean'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ];
        if ($isActive) {
            $rules['start_time'] = ['required', 'date_format:H:i'];
            $rules['end_time'] = ['required', 'date_format:H:i', 'after:start_time'];
        }
        $validated = $request->validate($rules);

        $schedule = ClinicalStaffWeeklySchedule::query()
            ->where('clinical_staff_id', $doctorId)
            ->where('weekday', $weekday)
            ->firstOrFail();

        $schedule->is_active = $isActive;
        if ($schedule->is_active) {
            $schedule->start_time = $validated['start_time'] ?? $schedule->start_time;
            $schedule->end_time = $validated['end_time'] ?? $schedule->end_time;
        } else {
            $schedule->start_time = null;
            $schedule->end_time = null;
        }
        $schedule->save();

        return redirect()
            ->route('clinical_staff.availability')
            ->with('success', 'Weekly schedule updated.');
    }

    public function toggleDay(Request $request, int $weekday): RedirectResponse
    {
        $doctorId = auth('clinical_staff')->id();
        abort_unless($weekday >= 1 && $weekday <= 7, 404);

        if ($weekday === AppointmentBookingRules::CLOSED_WEEKDAY && $request->boolean('is_active')) {
            return redirect()
                ->route('clinical_staff.availability')
                ->with('info', 'Sunday is closed for bookings and cannot be enabled.');
        }

        $this->ensureDefaultWeeklySchedule($doctorId);

        $schedule = ClinicalStaffWeeklySchedule::query()
            ->where('clinical_staff_id', $doctorId)
            ->where('weekday', $weekday)
            ->firstOrFail();

        $schedule->is_active = $request->boolean('is_active');
        if (! $schedule->is_active) {
            $schedule->start_time = null;
            $schedule->end_time = null;
        } elseif (! $schedule->start_time || ! $schedule->end_time) {
            $schedule->start_time = '09:00';
            $schedule->end_time = '17:00';
        }
        $schedule->save();

        return redirect()
            ->route('clinical_staff.availability')
            ->with('success', 'Day availability updated.');
    }

    public function storeBlockedDate(Request $request): RedirectResponse
    {
        $doctorId = auth('clinical_staff')->id();

        $validated = $request->validate([
            'blocked_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        ClinicalStaffBlockedDate::query()->updateOrCreate(
            [
                'clinical_staff_id' => $doctorId,
                'blocked_date' => $validated['blocked_date'],
            ],
            ['reason' => $validated['reason'] ?? null]
        );

        return redirect()
            ->route('clinical_staff.availability')
            ->with('success', 'Blocked date saved.');
    }

    public function destroyBlockedDate(ClinicalStaffBlockedDate $blockedDate): RedirectResponse
    {
        abort_unless((int) $blockedDate->clinical_staff_id === (int) auth('clinical_staff')->id(), 403);
        $blockedDate->delete();

        return redirect()
            ->route('clinical_staff.availability')
            ->with('success', 'Blocked date removed.');
    }

    private function ensureDefaultWeeklySchedule(int $doctorId): void
    {
        $exists = ClinicalStaffWeeklySchedule::query()->where('clinical_staff_id', $doctorId)->exists();
        if ($exists) {
            return;
        }

        for ($d = 1; $d <= 7; $d++) {
            $isUnavailable = $d >= 6 || $d === AppointmentBookingRules::CLOSED_WEEKDAY;
            ClinicalStaffWeeklySchedule::query()->create([
                'clinical_staff_id' => $doctorId,
                'weekday' => $d,
                'is_active' => ! $isUnavailable,
                'start_time' => $isWeekend ? null : '09:00:00',
                'end_time' => $isWeekend ? null : '17:00:00',
            ]);
        }
    }
}
