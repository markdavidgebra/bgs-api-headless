@extends('admin.layouts.master')

@section('content')
  @php
    $startGrid = $monthCursor->copy()->startOfMonth()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY);
    $endGrid = $monthCursor->copy()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SATURDAY);
    $calendarDays = collect();
    $cursor = $startGrid->copy();
    while ($cursor->lte($endGrid)) {
        $dateKey = $cursor->toDateString();
        $dayAppointments = collect($appointmentsByDate->get($dateKey, collect()))
            ->map(function ($appt) {
                $status = (string) ($appt->status ?? 'pending');
                $badge = match ($status) {
                    'confirmed' => 'bg-blue-lt',
                    'completed' => 'bg-green-lt',
                    'cancelled' => 'bg-red-lt',
                    'rescheduled' => 'bg-azure-lt',
                    default => 'bg-yellow-lt',
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
                    'showUrl' => route('admin.appointments.show', $appt->id),
                ];
            })
            ->values();

        $calendarDays->push([
            'date' => $cursor->copy(),
            'dateKey' => $dateKey,
            'isCurrentMonth' => $cursor->month === $monthCursor->month && $cursor->year === $monthCursor->year,
            'appointments' => $dayAppointments,
        ]);
        $cursor->addDay();
    }
    $calendarWeeks = $calendarDays->chunk(7);
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Appointments Calendar</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="h3 mb-0">{{ $monthCursor->format('F Y') }}</div>
            <div class="btn-list">
              <a href="{{ route('admin.appointments.calendar', ['month' => $prevMonth]) }}" class="btn">Previous</a>
              <a href="{{ route('admin.appointments.calendar', ['month' => now()->format('Y-m')]) }}" class="btn">Current month</a>
              <a href="{{ route('admin.appointments.calendar', ['month' => $nextMonth]) }}" class="btn">Next</a>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive">
          <table class="table table-bordered calendar-grid m-0">
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
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="{{ $isToday ? 'text-primary' : '' }}">{{ $day['date']->format('j') }}</strong>
                        @if ($isToday)
                          <span class="badge bg-primary-lt">Today</span>
                        @endif
                      </div>

                      @if ($dayCount > 0)
                        <button
                          type="button"
                          class="btn btn-sm {{ $btnClass }} mb-2 w-100 js-open-day-bookings"
                          data-date="{{ $day['date']->format('l, M j, Y') }}"
                          data-appointments='@json($day['appointments'])'
                        >
                          {{ $dayCount }} booking{{ $dayCount > 1 ? 's' : '' }}
                        </button>

                        <div class="small">
                          @foreach ($day['appointments']->take(2) as $row)
                            <div class="text-truncate">{{ $row['time'] }} · {{ $row['patient'] }}</div>
                          @endforeach
                          @if ($dayCount > 2)
                            <div class="text-secondary">+{{ $dayCount - 2 }} more</div>
                          @endif
                        </div>
                      @else
                        <div class="text-secondary small">No bookings</div>
                      @endif
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal fade" id="dayBookingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Bookings - <span id="dayBookingsDateLabel">Date</span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <div class="table-responsive">
                <table class="table table-vcenter table-sm mb-0">
                  <thead>
                    <tr>
                      <th>Time</th>
                      <th>Patient</th>
                      <th>Procedure</th>
                      <th>Doctor</th>
                      <th>Status</th>
                      <th class="w-1"></th>
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
        <div class="card mt-3">
          <div class="card-body text-secondary text-center py-4">
            No scheduled appointments for {{ $monthCursor->format('F Y') }}.
          </div>
        </div>
      @endif
    </div>
  </div>

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
                <td><span class="badge ${escapeHtml(row.badge || 'bg-secondary-lt')}">${escapeHtml(row.status || '—')}</span></td>
                <td><a href="${escapeHtml(row.showUrl || '#')}" class="btn btn-sm btn-primary">View</a></td>
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
@endsection
