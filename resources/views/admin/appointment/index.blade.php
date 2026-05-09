@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Appointments</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#appointment-filters" role="button" aria-expanded="true"
              aria-controls="appointment-filters">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5.5 5h13" />
                <path d="M5.5 12h13" />
                <path d="M5.5 19h13" />
                <path d="M4 5l0 .01" />
                <path d="M4 12l0 .01" />
                <path d="M4 19l0 .01" />
              </svg>
              Filters
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="appointment-filters" method="GET"
            action="{{ route('admin.appointments') }}">
            <div class="col-lg-6">
              <label class="form-label" for="search">Search</label>
              <div class="input-icon">
                <span class="input-icon-addon">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    <path d="M21 21l-6 -6" />
                  </svg>
                </span>
                <input id="search" type="text" class="form-control" name="search" placeholder="Patient, doctor, service…"
                  value="{{ request('search') }}">
              </div>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                <option value="rescheduled" @selected(request('status') === 'rescheduled')>Rescheduled</option>
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="date">Date</label>
              <input id="date" type="date" class="form-control" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
          </form>

        </div>
        <div id="table-default" class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th><button class="table-sort" data-sort="sort-patient">Patient</button></th>
                <th class="text-secondary"><button class="table-sort" data-sort="sort-doctor">Doctor</button></th>
                <th><button class="table-sort" data-sort="sort-service">Service</button></th>
                <th><button class="table-sort" data-sort="sort-date">Date</button></th>
                <th><button class="table-sort" data-sort="sort-time">Time</button></th>
                <th><button class="table-sort" data-sort="sort-status">Status</button></th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody class="table-tbody">
              @forelse ($appointments as $appointment)
                @php
                  $status = $appointment->status ?? 'pending';
                  $statusBadge = match ($status) {
                      'confirmed' => 'bg-blue-lt',
                      'completed' => 'bg-green-lt',
                      'cancelled' => 'bg-red-lt',
                      'rescheduled' => 'bg-azure-lt',
                      default => 'bg-yellow-lt',
                  };
                  $patientName = $appointment->patient?->name ?? '—';
                  $doctorName = $appointment->doctor?->name ?? '—';
                  $serviceName = $appointment->service?->name ?? '—';
                  $dateLabel = $appointment->appointment_date?->format('Y-m-d') ?? '—';
                  $timeRaw = $appointment->appointment_time;
                  $timeForSort = '00:00';
                  if ($timeRaw) {
                      $timeLabel = is_string($timeRaw) && strlen($timeRaw) >= 8
                          ? substr($timeRaw, 0, 5)
                          : \Illuminate\Support\Carbon::parse($timeRaw)->format('H:i');
                      $timeForSort = $timeLabel;
                  } else {
                      $timeLabel = '—';
                  }
                  $dateTs = strtotime($dateLabel . ' ' . $timeForSort) ?: 0;
                @endphp
                <tr>
                  <td class="sort-patient">
                    <div class="d-flex align-items-center">
                      <span class="avatar avatar-sm rounded bg-azure-lt text-azure me-2">
                        {{ strtoupper(substr($patientName, 0, 1)) }}
                      </span>
                      <div>
                        <div class="fw-medium">{{ $patientName }}</div>
                        <div class="text-secondary small">{{ $appointment->appointment_no }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-secondary sort-doctor">{{ $doctorName }}</td>
                  <td class="sort-service">{{ $serviceName }}</td>
                  <td class="sort-date" data-date="{{ $dateTs }}">{{ $dateLabel }}</td>
                  <td class="sort-time">{{ $timeLabel }}</td>
                  <td class="sort-status" data-status="{{ $status }}">
                    <span class="badge {{ $statusBadge }}">{{ ucfirst($status) }}</span>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.appointments.show', $appointment->id) }}"
                        class="btn btn-sm btn-primary">View</a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-secondary">
                    No appointments found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-secondary small">Tip: click headers to sort the current page.</div>
            {{ $appointments->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script src="{{ asset('admin/assets/dist/libs/list.js/dist/list.min.js') }}" defer></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const list = new List('table-default', {
          sortClass: 'table-sort',
          listClass: 'table-tbody',
          valueNames: [
            'sort-patient',
            'sort-doctor',
            { attr: 'data-date', name: 'sort-date' },
            'sort-time',
            'sort-service',
            { attr: 'data-status', name: 'sort-status' },
          ]
        });
      });
    </script>
  @endpush
@endsection