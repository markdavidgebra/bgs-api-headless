@extends('patient.layouts.master')

@section('title', 'Appointment details')

@section('content')
  @php
    $statusClass = fn (?string $s) => match ($s) {
        'completed' => 'text-success',
        'cancelled' => 'text-danger',
        'confirmed' => 'text-primary',
        'rescheduled' => 'text-info',
        default => 'text-warning',
    };

    $canChange = in_array($appointment->status ?? 'pending', ['pending', 'confirmed', 'rescheduled'], true)
        && ($appointment->appointment_date ? $appointment->appointment_date->isFuture() || $appointment->appointment_date->isToday() : false);
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span>
          <a href="{{ route('patient.appointments') }}">Appointments</a>
          <span></span> Details
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-4">
                    <div class="card-header p-0 pb-10 d-flex align-items-center justify-content-between flex-wrap">
                      <h3 class="mb-0">Appointment details</h3>
                      <a href="{{ route('patient.appointments') }}" class="font-sm">Back</a>
                    </div>
                    <div class="card-body p-0">
                      <p class="mb-0 text-muted font-sm">
                        Ref. {{ $appointment->appointment_no ?? '#' . $appointment->id }}
                        <span class="mx-2">&middot;</span>
                        <span class="{{ $statusClass($appointment->status) }}">{{ $appointment->status_label }}</span>
                      </p>
                    </div>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                  @endif

                  <div class="card">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Date</h6>
                          <p class="mb-0">{{ $appointment->date_display }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Time</h6>
                          <p class="mb-0">{{ $appointment->time_display }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Service</h6>
                          <p class="mb-0">{{ $appointment->service_name }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Clinical staff</h6>
                          <p class="mb-0">{{ $appointment->doctor_name }}</p>
                        </div>
                      </div>

                      <div class="d-flex flex-wrap mt-10">
                        @if ($canChange)
                          <a href="{{ route('patient.appointments.reschedule', $appointment) }}" class="btn btn-sm mr-10 mb-10">
                            <i class="fi-rs-refresh mr-5"></i>Reschedule
                          </a>
                          <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" class="mb-10">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this appointment?')">
                              <i class="fi-rs-cross mr-5"></i>Cancel
                            </button>
                          </form>
                        @endif
                      </div>
                    </div>
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

