@extends('clinical-staff.layouts.master')

@section('title', 'Clinical staff dashboard')

@section('content')
  @php($doctor = auth('doctor')->user())
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Dashboard
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
                  <div class="section-title">
                    <h3>Hello, {{ $doctor->name ?? 'Clinical staff' }}!</h3>
                    <p class="mb-15">
                      Overview of all clinic appointments and activity.
                    </p>
                  </div>

                  <div class="row mt-30">
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Today's Appointments</h6>
                          <h3 class="mb-0">{{ number_format($todayAppointmentsCount ?? 0) }}</h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Upcoming Appointments</h6>
                          <h3 class="mb-0">{{ number_format($upcomingAppointmentsCount ?? 0) }}</h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Patients Today</h6>
                          <h3 class="mb-0">{{ number_format($patientsTodayCount ?? 0) }}</h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Pending Notes</h6>
                          <h3 class="mb-0">{{ number_format($pendingNotesCount ?? 0) }}</h3>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row mt-10">
                    <div class="col-lg-8 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-header">
                          <h5 class="mb-0">Today's Schedule</h5>
                        </div>
                        <div class="card-body p-0">
                          <div class="table-responsive">
                            <table class="table mb-0">
                              <thead>
                                <tr>
                                  <th>Time</th>
                                  <th>Patient</th>
                                  <th>Clinician</th>
                                  <th>Service</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse (($scheduleToday ?? collect()) as $appointment)
                                  <tr>
                                    <td>{{ $appointment->time_display }}</td>
                                    <td>{{ $appointment->patient_name }}</td>
                                    <td>{{ $appointment->doctor_name }}</td>
                                    <td>{{ $appointment->service_name }}</td>
                                    <td><span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_label }}</span></td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No appointments scheduled today.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-4 mb-25">
                      <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">Notifications</h5>
                          <a href="{{ route('doctor.notifications') }}" class="small">Inbox
                            @if (($notificationsUnreadCount ?? 0) > 0)
                              ({{ $notificationsUnreadCount }} unread)
                            @endif
                          </a>
                        </div>
                        <div class="card-body">
                          @if (! empty($notifications))
                            <ul class="mb-3 ps-3">
                              @foreach ($notifications as $note)
                                <li class="mb-2">{{ $note }}</li>
                              @endforeach
                            </ul>
                          @else
                            <p class="text-secondary mb-3 mb-0">No dashboard alerts right now.</p>
                          @endif
                          <a href="{{ route('doctor.notifications') }}" class="btn btn-sm btn-outline-primary">Open notification inbox</a>
                        </div>
                      </div>

                      <div class="card mb-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">Upcoming Appointments</h5>
                          <a href="{{ route('doctor.appointments') }}" class="small">View all</a>
                        </div>
                        <div class="card-body">
                          @if (($upcomingAppointments ?? collect())->isNotEmpty())
                            <ul class="mb-0 ps-3">
                              @foreach ($upcomingAppointments as $appointment)
                                <li class="mb-2">
                                  {{ $appointment->date_display }} {{ $appointment->time_display }} — {{ $appointment->patient_name }}
                                  <span class="text-muted">({{ $appointment->doctor_name }})</span>
                                </li>
                              @endforeach
                            </ul>
                          @else
                            <p class="text-secondary mb-0">No upcoming appointments.</p>
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
    </div>
  </main>
@endsection
