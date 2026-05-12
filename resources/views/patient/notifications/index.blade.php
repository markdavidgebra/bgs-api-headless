@extends('patient.layouts.master')

@section('title', 'Notifications')

@section('content')
  <main class="main">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('patient.dashboard') }}" rel="nofollow">Dashboard</a>
          <span></span> Notifications
        </div>
      </div>
    </div>
    <section class="mt-50 mb-50">
      <div class="container">
        <div class="row">
          @include('patient.layouts.sidebar')
          <div class="col-md-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
              <h4 class="mb-0">Notifications</h4>
              @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('patient.notifications.read-all') }}" class="m-0">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all read</button>
                </form>
              @endif
            </div>

            @if (session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
              <div class="card-body">
                @forelse ($notifications as $n)
                  @php
                    $d = is_array($n->data) ? $n->data : [];
                    $title = $d['title'] ?? class_basename($n->type);
                    $message = $d['message'] ?? '';
                    $isUnread = $n->read_at === null;
                  @endphp
                  <div class="border-bottom py-3 @if ($isUnread) bg-light @endif">
                    <div class="d-flex justify-content-between gap-2">
                      <div>
                        <div class="fw-medium">{{ $title }}</div>
                        @if ($message !== '')
                          <div class="text-secondary small mt-1">{{ $message }}</div>
                        @endif
                        <div class="text-secondary small mt-1">{{ $n->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</div>
                      </div>
                      <div class="text-nowrap">
                        <form method="POST" action="{{ route('patient.notifications.read', $n->id) }}" class="d-inline">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-outline-primary">
                            @if ($isUnread)
                              {{ __('Open / mark read') }}
                            @else
                              {{ __('Open') }}
                            @endif
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                @empty
                  <p class="text-secondary mb-0">No notifications yet.</p>
                @endforelse
              </div>
            </div>

            @if ($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator && $notifications->hasPages())
              <div class="mt-3">
                {{ $notifications->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
