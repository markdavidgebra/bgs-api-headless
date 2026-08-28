@extends('patient.layouts.master')

@section('title', 'My appointments')

@section('content')
  @php
    $statusClass = fn (?string $s) => match ($s) {
        'completed' => 'text-success',
        'cancelled' => 'text-danger',
        'confirmed' => 'text-primary',
        'rescheduled' => 'text-info',
        default => 'text-warning',
    };

    $canChange = function ($appt): bool {
        $status = $appt->status ?? 'pending';
        $isChangeable = in_array($status, ['pending', 'confirmed', 'rescheduled'], true);
        $isFuture = $appt->appointment_date ? $appt->appointment_date->isFuture() || $appt->appointment_date->isToday() : false;

        return $isChangeable && $isFuture;
    };
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> Appointments
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
                      <div>
                        <h3 class="mb-0">My appointments</h3>
                        <p class="mb-0 text-muted font-sm">See what you booked, track status, and manage upcoming visits.</p>
                      </div>
                      <a href="{{ route('patient.appointments.book') }}" class="btn btn-sm mb-10">
                        <i class="fi-rs-calendar mr-5"></i>Book appointment
                      </a>
                    </div>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                  @endif

                  <div class="card mb-30">
                    <div class="card-header p-0 d-flex align-items-center justify-content-between flex-wrap">
                      <h3 class="mb-0">Upcoming appointments</h3>
                      <span class="font-sm text-muted">{{ $upcomingAppointments->count() }} scheduled</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="order_table table m-0 mt-20">
                          <thead>
                            <tr>
                              <th>Reference</th>
                              <th>Date</th>
                              <th>Time</th>
                              <th>Service</th>
                              <th>Clinical staff</th>
                              <th>Status</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($upcomingAppointments as $appt)
                              <tr>
                                <td>{{ $appt->appointment_no ?? '#' . $appt->id }}</td>
                                <td>{{ $appt->date_display }}</td>
                                <td>{{ $appt->time_display }}</td>
                                <td>{{ $appt->service_name }}</td>
                                <td>{{ $appt->clinical_staff_name }}</td>
                                <td><span class="{{ $statusClass($appt->status) }}">{{ $appt->status_label }}</span></td>
                                <td class="text-end">
                                  <a href="{{ route('patient.appointments.show', $appt) }}" class="btn btn-sm btn-outline-primary mb-5">
                                    View details
                                  </a>
                                  @if ($canChange($appt))
                                    <a href="{{ route('patient.appointments.reschedule', $appt) }}" class="btn btn-sm btn-outline-secondary mb-5">
                                      Reschedule
                                    </a>
                                    <form method="POST" action="{{ route('patient.appointments.cancel', $appt) }}" class="d-inline">
                                      @csrf
                                      <button type="submit" class="btn btn-sm btn-outline-danger mb-5" onclick="return confirm('Cancel this appointment?')">
                                        Cancel
                                      </button>
                                    </form>
                                  @endif
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="7" class="text-center text-muted py-40">
                                  No upcoming appointments yet.
                                  <a href="{{ route('appointment') }}">Book an appointment</a>.
                                </td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header p-0 d-flex align-items-center justify-content-between flex-wrap">
                      <h3 class="mb-0">Past appointments</h3>
                      <span class="font-sm text-muted">History</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="order_table table m-0 mt-20">
                          <thead>
                            <tr>
                              <th>Reference</th>
                              <th>Date</th>
                              <th>Time</th>
                              <th>Service</th>
                              <th>Clinical staff</th>
                              <th>Status</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($pastAppointments as $appt)
                              <tr>
                                <td>{{ $appt->appointment_no ?? '#' . $appt->id }}</td>
                                <td>{{ $appt->date_display }}</td>
                                <td>{{ $appt->time_display }}</td>
                                <td>{{ $appt->service_name }}</td>
                                <td>{{ $appt->clinical_staff_name }}</td>
                                <td><span class="{{ $statusClass($appt->status) }}">{{ $appt->status_label }}</span></td>
                                <td class="text-end">
                                  <a href="{{ route('patient.appointments.show', $appt) }}" class="btn btn-sm btn-outline-primary mb-5">
                                    View details
                                  </a>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="7" class="text-center text-muted py-40">No past appointments found.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <div class="mt-20">
                        {{ $pastAppointments->links() }}
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
