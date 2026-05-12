<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('patient.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $row = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($row->read_at === null) {
            $row->markAsRead();
        }

        $data = $row->data;
        if (is_array($data) && ! empty($data['action_url']) && is_string($data['action_url'])) {
            return redirect()->to($data['action_url']);
        }

        return redirect()->route('patient.notifications.index');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()
            ->route('patient.notifications.index')
            ->with('success', __('All notifications marked as read.'));
    }
}
