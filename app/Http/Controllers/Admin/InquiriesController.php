<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InquiriesController extends Controller
{
    public function index(): View
    {
        $inquiries = Inquiry::query()
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.inquiries.index', compact('inquiries'));
    }

    public function show(int|string $id): View
    {
        $inquiry = Inquiry::query()->findOrFail($id);

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $inquiry = Inquiry::query()->findOrFail($id);
        $inquiry->delete();

        return redirect()->route('admin.inquiries')->with('status', __('Inquiry deleted.'));
    }
}
