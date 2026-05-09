<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DoctorAccountCreatedMail;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class DoctorsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Doctor::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('specialty', 'like', "%{$term}%")
                    ->orWhere('bio', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%'.$request->string('specialty').'%');
        }

        $doctors = $query->paginate(15)->withQueryString();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Doctor::class],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $plainPassword = Str::password(12);

        $doctor = Doctor::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $plainPassword,
            'status' => 'active',
        ]);

        Mail::to($doctor->email)->send(new DoctorAccountCreatedMail($doctor, $plainPassword));

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('status', __('Doctor created successfully.'))
            ->with('temporary_password', $plainPassword);
    }

    public function show(int $id): View
    {
        $doctor = Doctor::query()
            ->with('weeklySchedules')
            ->findOrFail($id);

        // Example: Use real relationships when implemented.
        // For now, mock relationship data for the view
        $doctor->assigned_services = [
            'Facial Treatment',
            'Chemical peel',
            'Laser',
        ];

        $doctor->recent_appointments_sample = [
            ['code' => 'APT-0012', 'patient' => 'Maria Santos', 'date' => '2026-03-20', 'time' => '14:00', 'status' => 'Pending'],
            ['code' => 'APT-0010', 'patient' => 'Ana Reyes', 'date' => '2026-03-18', 'time' => '10:30', 'status' => 'Completed'],
        ];

        return view('admin.doctors.show', compact('doctor'));
    }
}
