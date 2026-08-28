@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Reports</div>
          <h2 class="page-title">Appointments report</h2>
          <div class="text-secondary small mt-1">Live data from appointments; filters apply to stats, charts, and the table.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.reports') }}" class="btn">Overview</a>
          <a href="{{ route('admin.appointments') }}" class="btn btn-primary">Appointments</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total</div>
              <div class="h3 mb-0">{{ number_format($total) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Completed</div>
              <div class="h3 mb-0">{{ number_format($completed) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Cancelled</div>
              <div class="h3 mb-0">{{ number_format($cancelled) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Pending</div>
              <div class="h3 mb-0">{{ number_format($pending) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Confirmed</div>
              <div class="h3 mb-0">{{ number_format($confirmed) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Rescheduled</div>
              <div class="h3 mb-0">{{ number_format($rescheduled) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">By date</h3>
            </div>
            <div class="card-body">
              @forelse ($byDate as $row)
                <div class="d-flex justify-content-between mb-2">
                  <span>{{ \Illuminate\Support\Carbon::parse($row->day)->format('M d, Y') }}</span>
                  <span class="text-secondary">{{ number_format((int) $row->c) }}</span>
                </div>
              @empty
                <div class="text-secondary small">No appointments in this range.</div>
              @endforelse
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">By status</h3>
            </div>
            <div class="card-body">
              @foreach ($byStatus as $status)
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="d-flex align-items-center gap-2">
                    <span class="badge {{ $status['class'] }} text-white">&nbsp;</span>
                    <span>{{ $status['label'] }}</span>
                  </span>
                  <span class="text-secondary">{{ number_format($status['value']) }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">By doctor</h3>
            </div>
            <div class="card-body">
              @forelse ($byDoctor as $doc)
                <div class="d-flex justify-content-between mb-2">
                  <span>{{ $doc->name }}</span>
                  <span class="text-secondary">{{ number_format((int) $doc->appointment_count) }}</span>
                </div>
              @empty
                <div class="text-secondary small">No doctor breakdown for this range.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.appointments') }}">
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="from">From</label>
              <input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="to">To</label>
              <input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="status">Status</label>
              <select id="status" name="status" class="form-select">
                <option value="">All</option>
                @foreach ($statusOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label" for="doctor_id">Clinical staff</label>
              <select id="doctor_id" name="doctor_id" class="form-select">
                <option value="">All</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>
                    {{ $doctor->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label" for="service_id">Service</label>
              <select id="service_id" name="service_id" class="form-select">
                <option value="">All</option>
                @foreach ($services as $service)
                  <option value="{{ $service->id }}" @selected((string) request('service_id') === (string) $service->id)>
                    {{ $service->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
          </form>
        </div>

        <div class="card-header border-top">
          <h3 class="card-title mb-0">Appointments</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Code</th>
                <th>Patient</th>
                <th>Clinical staff</th>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($appointments as $appt)
                <tr>
                  <td class="font-monospace">
                    <a href="{{ route('admin.appointments.show', $appt->id) }}">{{ $appt->appointment_no }}</a>
                  </td>
                  <td>{{ $appt->patient->name ?? '—' }}</td>
                  <td>{{ $appt->doctor->name ?? '—' }}</td>
                  <td>{{ $appt->service->name ?? '—' }}</td>
                  <td>{{ $appt->appointment_date?->format('M d, Y') ?? '—' }}</td>
                  <td>{{ $appt->time_display }}</td>
                  <td>
                    <span class="badge {{ $appt->status_badge }}">{{ $appt->status_label }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-secondary py-4">No appointments match the filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($appointments->hasPages())
          <div class="card-footer d-flex justify-content-center">
            {{ $appointments->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
