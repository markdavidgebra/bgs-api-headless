<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqsController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.faq.index', compact('faqs'));
    }

    public function create(): View
    {
        return view('admin.faq.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
        ]);

        Faq::query()->create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.faqs')->with('status', __('FAQ created.'));
    }

    public function edit(int|string $id): View
    {
        $faq = Faq::query()->findOrFail($id);

        return view('admin.faq.edit', compact('faq'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $faq = Faq::query()->findOrFail($id);

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.faqs')->with('status', __('FAQ updated.'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $faq = Faq::query()->findOrFail($id);
        $faq->delete();

        return redirect()->route('admin.faqs')->with('status', __('FAQ deleted.'));
    }
}
