@extends('clinical-staff.layouts.master')

@section('title', $notification->title)

@section('content')
  @php
    use Illuminate\Support\Str;
  @endphp
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span>
          <a href="{{ route('doctor.notifications') }}">Notifications</a>
          <span></span> {{ Str::limit($notification->title, 40) }}
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
                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif

                  <div class="d-flex align-items-start gap-3 mb-20">
                    <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center"
                      style="width: 52px; height: 52px;">
                      <i class="{{ $notification->icon_class }} fa-xl"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                      <p class="text-muted small text-uppercase fw-bold mb-5">{{ $notification->type_label }}</p>
                      <h3 class="mb-10">{{ $notification->title }}</h3>
                      <p class="text-secondary mb-0">{{ $notification->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                        <span class="text-muted">&middot; {{ $notification->created_at->diffForHumans() }}</span>
                      </p>
                    </div>
                  </div>

                  <div class="card mb-25">
                    <div class="card-body">
                      <p class="mb-0 lh-lg">{{ $notification->message }}</p>
                    </div>
                  </div>

                  <div class="card mb-25">
                    <div class="card-body">
                      <h6 class="text-uppercase text-muted small fw-bold mb-15">{{ __('Related') }}</h6>
                      <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary">{{ __('Patient') }}</dt>
                        <dd class="col-sm-8">
                          @if ($notification->patient)
                            <a href="{{ route('doctor.patient-records.show', $notification->patient) }}">{{ $notification->patient->name }}</a>
                          @else
                            &mdash;
                          @endif
                        </dd>
                        <dt class="col-sm-4 text-secondary mt-2">{{ __('Appointment') }}</dt>
                        <dd class="col-sm-8 mt-2 font-monospace">
                          @if ($notification->appointment)
                            <a href="{{ route('doctor.appointments.show', $notification->appointment) }}">{{ $notification->appointment->appointment_no }}</a>
                          @else
                            &mdash;
                          @endif
                        </dd>
                      </dl>
                    </div>
                  </div>

                  <div class="d-flex flex-wrap gap-2">
                    @if ($notification->primaryActionUrl())
                      <a href="{{ $notification->primaryActionUrl() }}" class="btn btn-primary">{{ $notification->primaryActionLabel() }}</a>
                    @endif
                    @if ($notification->patient)
                      <a href="{{ route('doctor.patient-records.show', $notification->patient) }}" class="btn btn-outline-secondary">{{ __('View patient') }}</a>
                    @endif
                    @if ($notification->appointment)
                      <a href="{{ route('doctor.appointments.show', $notification->appointment) }}" class="btn btn-outline-secondary">{{ __('View appointment') }}</a>
                    @endif
                    <a href="{{ route('doctor.notifications') }}" class="btn btn-light border">{{ __('Back to list') }}</a>
                    <form action="{{ route('doctor.notifications.destroy', $notification) }}" method="post" class="ms-auto"
                      onsubmit="return confirm('{{ __('Remove this notification?') }}');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-link text-danger">{{ __('Delete') }}</button>
                    </form>
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
