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

    .doctor-appt-calendar .table-responsive {
      -webkit-overflow-scrolling: touch;
    }

    .doctor-appt-calendar table.calendar-grid {
      table-layout: fixed;
      /* Prevent ultra-narrow columns (e.g. tablet + sidebar) from crushing text into a vertical strip */
      min-width: 720px;
      width: 100%;
    }

    .doctor-appt-calendar table.calendar-grid thead th {
      text-align: center;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #6b7280;
      background: #f9fafb;
      border-color: #e5e7eb;
      padding: 8px 6px !important;
    }

    .doctor-appt-calendar table.calendar-grid td {
      border-color: #e5e7eb;
      min-height: 118px;
      width: 14.28%;
      vertical-align: top !important;
      padding: 8px 6px !important;
    }

    .doctor-appt-calendar .calendar-day-cell-inner {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 6px;
      min-height: 102px;
    }

    .doctor-appt-calendar .calendar-day-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      flex-wrap: wrap;
    }

    .doctor-appt-calendar .js-open-day-bookings {
      font-weight: 700;
      display: block;
      width: 100%;
      white-space: normal !important;
      writing-mode: horizontal-tb !important;
      text-orientation: mixed !important;
      line-height: 1.25;
      font-size: 12px;
      padding: 7px 6px !important;
      text-align: center;
      word-break: normal;
      overflow-wrap: anywhere;
    }

    .doctor-appt-calendar .calendar-day-preview {
      font-size: 11px;
      line-height: 1.35;
    }

    .doctor-appt-calendar .calendar-day-preview .calendar-day-preview-line {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 100%;
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

              <div class="col-12">
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
                        <input type="hidden" name="view" value="{{ $viewMode }}">
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
                            <a href="{{ route('doctor.appointments', array_merge(request()->query(), ['view' => 'calendar'])) }}"
                              class="btn btn-sm view-btn {{ $viewMode === 'calendar' ? 'is-active' : '' }}">
                              Calendar view
                            </a>
                            <a href="{{ route('doctor.appointments', array_merge(request()->query(), ['view' => 'table'])) }}"
                              class="btn btn-sm view-btn {{ $viewMode === 'table' ? 'is-active' : '' }}">
                              Table view
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

                  @if ($viewMode === 'calendar' && $monthCursor && $appointmentsByDate)
                    @php
                      $startGrid = $monthCursor->copy()->startOfMonth()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY);
                      $endGrid = $monthCursor->copy()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SATURDAY);
                      $calendarDayCells = collect();
                      $cursor = $startGrid->copy();
                      while ($cursor->lte($endGrid)) {
                          $dateKey = $cursor->toDateString();
                          $dayAppointments = collect($appointmentsByDate->get($dateKey, collect()))
                              ->map(function ($appt) {
                                  $status = (string) ($appt->status ?? 'pending');
                                  $badge = match ($status) {
                                      'confirmed' => 'bg-primary',
                                      'completed' => 'bg-success',
                                      'cancelled' => 'bg-danger',
                                      'rescheduled' => 'bg-info text-dark',
                                      default => 'bg-warning text-dark',
                                  };
                                  $timeRaw = $appt->appointment_time;
                                  $timeLabel = $timeRaw
                                      ? (is_string($timeRaw) && strlen($timeRaw) >= 8
                                          ? substr($timeRaw, 0, 5)
                                          : \Illuminate\Support\Carbon::parse($timeRaw)->format('H:i'))
                                      : '—';

                                  return [
                                      'id' => (int) $appt->id,
                                      'time' => $timeLabel,
                                      'patient' => (string) ($appt->patient?->name ?? '—'),
                                      'procedure' => (string) ($appt->service?->name ?? '—'),
                                      'doctor' => (string) ($appt->doctor?->name ?? '—'),
                                      'status' => ucfirst($status),
                                      'badge' => $badge,
                                      'showUrl' => $appt->patient_id
                                          ? route('doctor.patient-records.show', $appt->patient_id)
                                          : route('doctor.appointments.show', $appt->id),
                                  ];
                              })
                              ->values();

                          $calendarDayCells->push([
                              'date' => $cursor->copy(),
                              'dateKey' => $dateKey,
                              'isCurrentMonth' => $cursor->month === $monthCursor->month && $cursor->year === $monthCursor->year,
                              'appointments' => $dayAppointments,
                          ]);
                          $cursor->addDay();
                      }
                      $calendarWeeks = $calendarDayCells->chunk(7);
                    @endphp

                    <div class="card mb-25 doctor-appt-calendar">
                      <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-20">
                          <div class="h4 mb-0">{{ $monthCursor->format('F Y') }}</div>
                          <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('doctor.appointments', array_merge(request()->except('page'), ['view' => 'calendar', 'month' => $prevMonth])) }}"
                              class="btn btn-sm btn-outline">Previous</a>
                            <a href="{{ route('doctor.appointments', array_merge(request()->except('page'), ['view' => 'calendar', 'month' => now()->format('Y-m')])) }}"
                              class="btn btn-sm btn-outline">Current month</a>
                            <a href="{{ route('doctor.appointments', array_merge(request()->except('page'), ['view' => 'calendar', 'month' => $nextMonth])) }}"
                              class="btn btn-sm btn-outline">Next</a>
                          </div>
                        </div>

                        <div class="table-responsive">
                          <table class="table table-bordered calendar-grid mb-0">
                            <thead>
                              <tr>
                                <th>Sunday</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($calendarWeeks as $week)
                                <tr>
                                  @foreach ($week as $day)
                                    @php
                                      $isToday = $day['dateKey'] === now()->toDateString();
                                      $dayCount = $day['appointments']->count();
                                      $btnClass = $dayCount > 0 ? 'btn-primary' : 'btn-outline-secondary';
                                    @endphp
                                    <td class="align-top p-2 {{ $day['isCurrentMonth'] ? '' : 'bg-light text-secondary' }}">
                                      <div class="calendar-day-cell-inner">
                                        <div class="calendar-day-header">
                                          <strong class="{{ $isToday ? 'text-primary' : '' }}">{{ $day['date']->format('j') }}</strong>
                                          @if ($isToday)
                                            <span class="badge bg-primary">Today</span>
                                          @endif
                                        </div>

                                        @if ($dayCount > 0)
                                          <button
                                            type="button"
                                            class="btn btn-sm {{ $btnClass }} js-open-day-bookings"
                                            data-date="{{ $day['date']->format('l, M j, Y') }}"
                                            data-appointments='@json($day['appointments'])'
                                            aria-label="{{ $dayCount }} booking{{ $dayCount > 1 ? 's' : '' }} for {{ $day['date']->format('M j') }}"
                                          >
                                            {{ $dayCount }} booking{{ $dayCount > 1 ? 's' : '' }}
                                          </button>

                                          <div class="calendar-day-preview text-secondary">
                                            @foreach ($day['appointments']->take(2) as $row)
                                              <div class="calendar-day-preview-line">{{ $row['time'] }} · {{ $row['patient'] }}</div>
                                            @endforeach
                                            @if ($dayCount > 2)
                                              <div>+{{ $dayCount - 2 }} more</div>
                                            @endif
                                          </div>
                                        @else
                                          <div class="text-secondary small">No bookings</div>
                                        @endif
                                      </div>
                                    </td>
                                  @endforeach
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="dayBookingsModal" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Bookings — <span id="dayBookingsDateLabel">Date</span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body p-0">
                            <div class="table-responsive">
                              <table class="table table-sm mb-0">
                                <thead class="table-light">
                                  <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Procedure</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 1%"> </th>
                                  </tr>
                                </thead>
                                <tbody id="dayBookingsRows">
                                  <tr>
                                    <td colspan="6" class="text-secondary text-center py-4">No bookings.</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    @if ($appointmentsByDate->isEmpty())
                      <div class="card mb-25">
                        <div class="card-body text-secondary text-center py-4">
                          No scheduled appointments for {{ $monthCursor->format('F Y') }} with the current filters.
                        </div>
                      </div>
                    @endif

                    @push('scripts')
                      <script>
                        document.addEventListener('DOMContentLoaded', function () {
                          const modalEl = document.getElementById('dayBookingsModal');
                          const rowsEl = document.getElementById('dayBookingsRows');
                          const dateLabelEl = document.getElementById('dayBookingsDateLabel');
                          if (!modalEl || !rowsEl || !dateLabelEl) return;

                          const modal = new bootstrap.Modal(modalEl);

                          function escapeHtml(value) {
                            return String(value)
                              .replaceAll('&', '&amp;')
                              .replaceAll('<', '&lt;')
                              .replaceAll('>', '&gt;')
                              .replaceAll('"', '&quot;')
                              .replaceAll("'", '&#039;');
                          }

                          function renderRows(appointments) {
                            if (!appointments || appointments.length === 0) {
                              rowsEl.innerHTML = '<tr><td colspan="6" class="text-secondary text-center py-4">No bookings.</td></tr>';
                              return;
                            }

                            rowsEl.innerHTML = appointments.map(function (row) {
                              return `
              <tr>
                <td class="font-monospace">${escapeHtml(row.time || '—')}</td>
                <td>${escapeHtml(row.patient || '—')}</td>
                <td>${escapeHtml(row.procedure || '—')}</td>
                <td>${escapeHtml(row.doctor || '—')}</td>
                <td><span class="badge ${escapeHtml(row.badge || 'bg-secondary')}">${escapeHtml(row.status || '—')}</span></td>
                <td class="text-end"><a href="${escapeHtml(row.showUrl || '#')}" class="btn btn-sm btn-primary">View</a></td>
              </tr>
            `;
                            }).join('');
                          }

                          document.querySelectorAll('.js-open-day-bookings').forEach(function (button) {
                            button.addEventListener('click', function () {
                              const date = button.getAttribute('data-date') || 'Date';
                              const payload = button.getAttribute('data-appointments') || '[]';
                              let appointments = [];
                              try {
                                appointments = JSON.parse(payload);
                              } catch (e) {
                                appointments = [];
                              }

                              dateLabelEl.textContent = date;
                              renderRows(appointments);
                              modal.show();
                            });
                          });
                        });
                      </script>
                    @endpush
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
