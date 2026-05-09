<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'number' => ['required', 'string', 'max:100'],
            'date' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:10000'],
        ]);

        Inquiry::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['number'],
            'preferred_date' => $validated['date'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        return redirect()->route('contact')->with('inquiry_sent', true);
    }
}
