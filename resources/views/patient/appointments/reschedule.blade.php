@extends('patient.layouts.master')

@section('title', 'Reschedule appointment')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span>
          <a href="{{ route('patient.appointments') }}">Appointments</a>
          <span></span> Reschedule
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
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">Reschedule appointment</h3>
                    </div>
                    <div class="card-body p-0">
                      <p class="mb-0">
                        Updating your date/time will mark this appointment as <strong>rescheduled</strong>.
                      </p>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-body">
                      <form method="POST" action="{{ route('patient.appointments.reschedule.update', $appointment) }}">
                        @csrf

                        <div class="row">
                          <div class="col-md-6 mb-15">
                            <label class="font-sm mb-5">Date</label>
                            <input
                              id="appointment_date"
                              type="date"
                              name="appointment_date"
                              value="{{ old('appointment_date', optional($appointment->appointment_date)->format('Y-m-d')) }}"
                              class="form-control @error('appointment_date') is-invalid @enderror"
                              min="{{ now()->toDateString() }}"
                              required
                            />
                            <p class="text-muted font-sm mt-5 mb-0">Sundays are not available for booking.</p>
                            @error('appointment_date')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>

                          <div class="col-md-6 mb-15">
                            <label class="font-sm mb-5">Time</label>
                            <input
                              type="time"
                              name="appointment_time"
                              value="{{ old('appointment_time', is_string($appointment->appointment_time) ? substr($appointment->appointment_time, 0, 5) : '') }}"
                              class="form-control @error('appointment_time') is-invalid @enderror"
                              required
                            />
                            @error('appointment_time')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>
                        </div>

                        <div class="d-flex flex-wrap mt-10">
                          <button type="submit" class="btn btn-sm mr-10 mb-10">
                            <i class="fi-rs-refresh mr-5"></i>Save changes
                          </button>
                          <a href="{{ route('patient.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary mb-10">
                            Back to details
                          </a>
                        </div>
                      </form>
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
  @include('partials.block-sunday-date-input')
  <script>
    (function () {
      var dateInput = document.getElementById('appointment_date');
      if (window.blockSundayDateInput) window.blockSundayDateInput(dateInput);
    })();
  </script>
@endsection

