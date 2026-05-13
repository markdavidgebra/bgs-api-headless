@extends('doctor.layouts.master')

@section('title', 'Appointments')

@section('content')
  <style>
    .actions-wrap .action-btn {
      border: 1px solid transparent;
      border-radius: 6px;
      color: #fff !important;
      font-size: 12px;
      font-weight: 700;
      line-height: 1.2;
      padding: 7px 10px;
      white-space: nowrap;
    }

    .actions-wrap .action-view {
      background: #1f2937;
    }

    .actions-wrap .action-start {
      background: #2563eb;
    }

    .actions-wrap .action-complete {
      background: #16a34a;
    }

    .actions-wrap .action-notes {
      background: #7c3aed;
    }

    .actions-wrap .action-reschedule {
      background: #d97706;
    }

    .actions-wrap .action-noshow {
      background: #dc2626;
    }

    .actions-wrap .action-btn:hover,
    .actions-wrap .action-btn:focus {
      color: #fff !important;
      filter: brightness(0.92);
    }

    .status-cell {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 10px;
      border: 1px solid #e5e7eb;
      border-radius: 999px;
      background: #f9fafb;
    }

    .status-cell .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: currentColor;
      opacity: 0.9;
    }

    .status-cell .status-text {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.2px;
      text-transform: capitalize;
      line-height: 1;
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, minmax(0, 1fr));
      gap: 10px;
    }

    .calendar-day {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #fff;
      min-height: 180px;
      padding: 10px;
    }

    .calendar-day-head {
      font-size: 12px;
      font-weight: 700;
      color: #4b5563;
      margin-bottom: 8px;
    }

    .calendar-item {
      border-radius: 6px;
      border: 1px solid #e5e7eb;
      padding: 6px 8px;
      margin-bottom: 6px;
      font-size: 12px;
      background: #f9fafb;
    }

    .timeline-hour-row {
      border-bottom: 1px solid #eef2f7;
      padding: 10px 0;
    }

    .timeline-hour-label {
      font-size: 12px;
      font-weight: 700;
      color: #6b7280;
      width: 70px;
      flex-shrink: 0;
    }

    .view-switch .view-btn {
      border-radius: 8px;
      font-weight: 700;
      font-size: 12px;
      padding: 8px 12px;
      border: 1px solid #cbd5e1;
      color: #1f2937 !important;
      background: #ffffff;
    }

    .view-switch .view-btn:hover,
    .view-switch .view-btn:focus {
      color: #111827 !important;
      border-color: #94a3b8;
      background: #f8fafc;
    }

    .view-switch .view-btn.is-active {
      border-color: #1d4ed8;
      background: #1d4ed8;
      color: #fff !important;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Appointments
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')

              <div class="col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20">
                    <h3>Appointments</h3>
                    <p class="mb-0">All clinic appointments. Use filters to narrow by date, status, or patient.</p>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif

                  @if ($errors->any())
                    <div class="alert alert-danger mb-20">
                      <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <div class="card mb-25">
                    <div class="card-body">
                      <form method="GET" action="{{ route('doctor.appointments') }}" class="row g-3">
                        <div class="col-lg-3 col-md-6">
                          <label for="date_filter" class="form-label">Date filter</label>
                          <select name="date_filter" id="date_filter" class="form-control">
                            <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="tomorrow" {{ $dateFilter === 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
                            <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom</option>
                          </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                          <label for="custom_date" class="form-label">Custom date</label>
                          <input type="date" name="custom_date" id="custom_date" class="form-control"
                            value="{{ $customDate }}">
                        </div>

                        <div class="col-lg-3 col-md-6">
                          <label for="search" class="form-label">Search patient</label>
                          <input type="text" name="search" id="search" class="form-control"
                            placeholder="Enter patient name" value="{{ $search }}">
                        </div>

                        <div class="col-lg-3 col-md-6">
                          <label for="status" class="form-label">Status</label>
                          <select name="status" id="status" class="form-control">
                            <option value="">All status</option>
                            @foreach ($statusOptions as $key => $label)
                              <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2 align-items-center mt-2">
                          <button type="submit" class="btn btn-sm">Apply filters</button>
                          <a href="{{ route('doctor.appointments') }}" class="btn btn-sm btn-outline">Reset</a>

                          <div class="ms-auto d-flex gap-2 view-switch">
                            <a href="{{ route('doctor.appointments', array_merge(request()->query(), ['view' => 'table'])) }}"
                              class="btn btn-sm view-btn {{ $viewMode === 'table' ? 'is-active' : '' }}">
                              Table view
                            </a>
                            <a href="{{ route('doctor.appointments', array_merge(request()->query(), ['view' => 'calendar'])) }}"
                              class="btn btn-sm view-btn {{ $viewMode === 'calendar' ? 'is-active' : '' }}">
                              Calendar view
                            </a>
                            <a href="{{ route('doctor.appointments', array_merge(request()->query(), ['view' => 'timeline'])) }}"
                              class="btn btn-sm view-btn {{ $viewMode === 'timeline' ? 'is-active' : '' }}">
                              Timeline view
                            </a>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>

                  @if ($viewMode === 'calendar')
                    <div class="card mb-25">
                      <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Weekly Schedule</h5>
                        <span class="small text-muted">{{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}</span>
                      </div>
                      <div class="card-body">
                        <div class="calendar-grid">
                          @foreach ($calendarDays as $day)
                            <div class="calendar-day">
                              <div class="calendar-day-head">{{ $day['date']->format('D, M d') }}</div>
                              @forelse ($day['appointments'] as $appointment)
                                <div class="calendar-item">
                                  <div><strong>{{ $appointment->time_display }}</strong> - {{ $appointment->patient_name }}</div>
                                  <div class="text-muted">{{ $appointment->service_name }} · {{ $appointment->doctor_name }}</div>
                                </div>
                              @empty
                                <p class="small text-muted mb-0">No appointments</p>
                              @endforelse
                            </div>
                          @endforeach
                        </div>
                        <p class="small text-muted mt-3 mb-0">Drag & drop rescheduling can be added later.</p>
                      </div>
                    </div>
                  @elseif ($viewMode === 'timeline')
                    <div class="card mb-25">
                      <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Timeline View</h5>
                        <span class="small text-muted">{{ \Illuminate\Support\Carbon::parse($timelineDate)->format('M d, Y') }}</span>
                      </div>
                      <div class="card-body">
                        @foreach ($timelineHours as $hour)
                          @php
                            $slotAppointments = $timelineAppointments->filter(function ($appointment) use ($hour) {
                              return (int) \Illuminate\Support\Carbon::parse((string) $appointment->appointment_time)->format('G') === (int) $hour;
                            })->values();
                          @endphp
                          <div class="d-flex timeline-hour-row">
                            <div class="timeline-hour-label">{{ \Illuminate\Support\Carbon::createFromTime($hour)->format('ga') }}</div>
                            <div class="flex-grow-1">
                              @forelse ($slotAppointments as $appointment)
                                <div class="calendar-item mb-2">
                                  <div><strong>{{ $appointment->time_display }}</strong> - {{ $appointment->patient_name }}</div>
                                  <div class="text-muted">{{ $appointment->service_name }} · {{ $appointment->doctor_name }} · {{ $appointment->status_label }}</div>
                                </div>
                              @empty
                                <span class="small text-muted">No schedule</span>
                              @endforelse
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  @endif

                  @if ($viewMode === 'table')
                    <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Appointments List</h5>
                      <span class="text-muted small">{{ $appointments->total() }} total</span>
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
                              <th>Notes</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($appointments as $appointment)
                              @php
                                $status = strtolower((string) $appointment->status);
                                $canStart = in_array($status, ['pending', 'rescheduled'], true);
                                $canComplete = in_array($status, ['confirmed', 'rescheduled'], true);
                              @endphp
                              <tr>
                                <td>
                                  <div>{{ $appointment->time_display }}</div>
                                  <small class="text-muted">{{ $appointment->date_display }}</small>
                                </td>
                                <td>{{ $appointment->patient_name }}</td>
                                <td>{{ $appointment->doctor_name }}</td>
                                <td>{{ $appointment->service_name }}</td>
                                <td>
                                  <div class="status-cell {{ $appointment->status_badge }}">
                                    <span class="status-dot"></span>
                                    <span class="status-text">{{ $appointment->status_label ?: ucfirst((string) $appointment->status) }}</span>
                                  </div>
                                </td>
                                <td style="min-width: 220px;">
                                  {{ \Illuminate\Support\Str::limit((string) optional($appointment->note)->doctor_notes, 70, '...') ?: 'No notes yet' }}
                                </td>
                                <td style="min-width: 320px;">
                                  <div class="d-flex flex-wrap gap-2 actions-wrap">
                                    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-xs action-btn action-view">
                                      View
                                    </a>

                                    @if ($canStart)
                                      <form method="POST" action="{{ route('doctor.appointments.start-session', $appointment) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-xs action-btn action-start">Start Session</button>
                                      </form>
                                    @endif

                                    @if ($canComplete)
                                      <form method="POST" action="{{ route('doctor.appointments.complete', $appointment) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-xs action-btn action-complete">Mark as Completed</button>
                                      </form>
                                    @endif

                                    <a href="{{ route('doctor.appointments.notes.create', $appointment) }}"
                                      class="btn btn-xs action-btn action-notes">
                                      Add Notes
                                    </a>

                                    <button type="button" class="btn btn-xs action-btn action-reschedule"
                                      onclick="document.getElementById('reschedule-{{ $appointment->id }}').classList.toggle('d-none')">
                                      Reschedule
                                    </button>

                                    <form method="POST" action="{{ route('doctor.appointments.mark-no-show', $appointment) }}"
                                      onsubmit="return confirm('Mark this appointment as no-show?')">
                                      @csrf
                                      <button type="submit" class="btn btn-xs action-btn action-noshow">Mark No-show</button>
                                    </form>
                                  </div>
                                </td>
                              </tr>

                              <tr id="notes-{{ $appointment->id }}" class="d-none">
                                <td colspan="8">
                                  <form method="POST" action="{{ route('doctor.appointments.notes', $appointment) }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-9">
                                      <label class="form-label mb-1">Doctor Notes</label>
                                      <textarea name="doctor_notes" rows="2" class="form-control"
                                        placeholder="Write short notes for this appointment...">{{ old('doctor_notes', optional($appointment->note)->doctor_notes) }}</textarea>
                                    </div>
                                    <div class="col-md-3">
                                      <button type="submit" class="btn btn-sm w-100">Save Notes</button>
                                    </div>
                                  </form>
                                </td>
                              </tr>

                              <tr id="reschedule-{{ $appointment->id }}" class="d-none">
                                <td colspan="8">
                                  <form method="POST" action="{{ route('doctor.appointments.reschedule', $appointment) }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-4">
                                      <label class="form-label mb-1">New date</label>
                                      <input type="date" name="appointment_date" class="form-control"
                                        value="{{ old('appointment_date', optional($appointment->appointment_date)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                      <label class="form-label mb-1">New time</label>
                                      <input type="time" name="appointment_time" class="form-control"
                                        value="{{ old('appointment_time', substr((string) $appointment->appointment_time, 0, 5)) }}">
                                    </div>
                                    <div class="col-md-4">
                                      <button type="submit" class="btn btn-sm w-100">Save Reschedule</button>
                                    </div>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="8" class="text-center text-secondary py-4">No appointments found for the
                                  selected filters.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="card-footer">
                      {{ $appointments->links() }}
                    </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
