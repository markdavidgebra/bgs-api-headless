<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PatientProfileController extends Controller
{
    public function index(): View
    {
        return view('patient.profile.index', [
            'patient' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $patient = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($patient->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'skin_type' => ['nullable', 'string', 'max:255'],
            'skin_concerns' => ['nullable', 'string', 'max:1000'],
            'recovery_time' => ['nullable', 'string', 'max:255'],
            'max_appointments_per_day' => ['nullable', 'integer', 'min:0', 'max:20'],
            'history_summary' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $patient->fill($validated);
        $patient->save();

        return redirect()->route('patient.profile')->with('success', 'Profile updated successfully.');
    }
}
