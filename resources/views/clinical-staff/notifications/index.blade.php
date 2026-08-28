@extends('clinical-staff.layouts.master')

@section('title', 'Notifications')

@section('content')
  @php
    use Illuminate\Support\Str;
    $tabLinks = [
        'all' => 'All',
        'unread' => 'Unread',
        'appointments' => 'Appointments',
        'follow_ups' => 'Follow-ups',
        'reminders' => 'Reminders',
    ];
  @endphp
  <style>
    .notif-filter .nav-link {
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
      padding: 8px 16px;
      color: #475569;
      border: 1px solid #e2e8f0;
      background: #fff;
      margin-right: 8px;
      margin-bottom: 8px;
    }

    .notif-filter .nav-link:hover {
      border-color: #cbd5e1;
      color: #0f172a;
    }

    .notif-filter .nav-link.active {
      background: #1d4ed8;
      border-color: #1d4ed8;
      color: #fff !important;
    }

    .notif-card {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      transition: box-shadow 0.15s ease;
    }

    .notif-card:not(.notif-card--read):hover {
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .notif-card--unread {
      border-left: 4px solid #2563eb;
      background: #f8fafc;
    }

    .notif-icon-wrap {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: #fff;
      border: 1px solid #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .notif-badge-read {
      background: #e2e8f0;
      color: #475569;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      padding: 4px 8px;
      border-radius: 999px;
    }

    .notif-badge-unread {
      background: #dbeafe;
      color: #1d4ed8;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      padding: 4px 8px;
      border-radius: 999px;
    }

    .notif-action-btn {
      font-size: 12px;
      font-weight: 700;
      padding: 6px 12px;
      border-radius: 8px;
      white-space: nowrap;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Notifications
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('clinical-staff.layouts.sidebar')

              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex flex-wrap flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
                    <div>
                      <h3 class="mb-5">Notifications</h3>
                      <p class="mb-0 text-secondary">Appointment updates, reminders, and follow-ups &mdash; not billing or admin reports.</p>
                    </div>
                    @if ($unreadCount > 0)
                      <span class="badge rounded-pill bg-primary">{{ $unreadCount }} unread</span>
                    @endif
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif

                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-20">
                    <ul class="nav notif-filter flex-wrap mb-0">
                      @foreach ($tabLinks as $key => $label)
                        <li class="nav-item">
                          <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                            href="{{ route('doctor.notifications', ['tab' => $key]) }}">{{ $label }}</a>
                        </li>
                      @endforeach
                    </ul>
                    <div class="d-flex flex-wrap gap-2">
                      <form action="{{ route('doctor.notifications.mark-all-read') }}" method="post" class="m-0">
                        @csrf
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary"
                          {{ $unreadCount === 0 ? 'disabled' : '' }}>Mark all as read</button>
                      </form>
                      <form action="{{ route('doctor.notifications.clear-read') }}" method="post" class="m-0"
                        onsubmit="return confirm('{{ __('Remove all notifications you have already read?') }}');">
                        @csrf
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Clear read notifications</button>
                      </form>
                    </div>
                  </div>

                  <div class="d-flex flex-column gap-3">
                    @forelse ($notifications as $notification)
                      @php
                        $url = $notification->primaryActionUrl() ?? route('doctor.notifications.show', $notification);
                      @endphp
                      <div
                        class="notif-card p-20 {{ $notification->is_read ? 'notif-card--read' : 'notif-card--unread' }}">
                        <div class="row g-3 align-items-start">
                          <div class="col-auto">
                            <div class="notif-icon-wrap">
                              <i class="{{ $notification->icon_class }} fa-lg"></i>
                            </div>
                          </div>
                          <div class="col min-w-0">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-5">
                              <h6 class="mb-0 fw-bold">{{ $notification->title }}</h6>
                              <span class="{{ $notification->is_read ? 'notif-badge-read' : 'notif-badge-unread' }}">
                                {{ $notification->is_read ? __('Read') : __('Unread') }}
                              </span>
                            </div>
                            <p class="text-secondary mb-8 small">{{ Str::limit($notification->message, 160) }}</p>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                              <span class="text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                              <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('doctor.notifications.show', $notification) }}"
                                  class="btn btn-sm btn-light notif-action-btn border">{{ __('Details') }}</a>
                                <a href="{{ $url }}" class="btn btn-sm btn-primary notif-action-btn">{{ $notification->primaryActionLabel() }}</a>
                                <form action="{{ route('doctor.notifications.destroy', $notification) }}" method="post" class="d-inline m-0"
                                  onsubmit="return confirm('{{ __('Remove this notification?') }}');">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-sm btn-link text-danger p-0 align-baseline">{{ __('Delete') }}</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    @empty
                      <div class="card mb-0">
                        <div class="card-body text-center py-5 text-secondary">
                          {{ __('No notifications in this view.') }}
                        </div>
                      </div>
                    @endforelse
                  </div>

                  <div class="mt-25">
                    {{ $notifications->links() }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
