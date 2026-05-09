@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Reports</div>
          <h2 class="page-title">Services report</h2>
          <div class="text-secondary small mt-1">
            Bookings and cancellations use <strong>appointment</strong> dates in range. Revenue sums <strong>payments</strong> with reference type <strong>appointment</strong> (paid + partial only).
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.reports') }}" class="btn">Overview</a>
          <a href="{{ route('admin.services') }}" class="btn btn-primary">Services</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-lg-3 col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Most booked</div>
              <div class="h3 mb-0">{{ $mostBookedName }}</div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Least booked</div>
              <div class="h3 mb-0">{{ $leastBookedName }}</div>
              <div class="text-secondary small mt-1">Among services with ≥1 booking</div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Mean revenue / service</div>
              <div class="h3 mb-0">₱{{ number_format($avgRevenue, 2) }}</div>
              <div class="text-secondary small mt-1">Listed catalog rows only</div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Most cancellations</div>
              <div class="h3 mb-0">{{ $highestCancellationName }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.services') }}">
            <div class="col-md-4 col-lg-3">
              <label class="form-label" for="from">Appointment from</label>
              <input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-4 col-lg-3">
              <label class="form-label" for="to">to</label>
              <input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-4 col-lg-3">
              <label class="form-label" for="catalog_status">Catalog status</label>
              <select id="catalog_status" name="catalog_status" class="form-select">
                <option value="">All</option>
                @foreach ($catalogStatusOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('catalog_status') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-2 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
          </form>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Top services by bookings</h3>
            </div>
            <div class="card-body">
              @forelse ($topByBookings as $row)
                @php
                  $pct = $maxBookings > 0 ? round(($row['bookings'] / $maxBookings) * 100) : 0;
                @endphp
                <div class="mb-3">
                  <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $row['name'] }}</span>
                    <span>{{ number_format($row['bookings']) }} bookings</span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%" role="progressbar"
                      aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              @empty
                <div class="text-secondary small">No booking data for this range.</div>
              @endforelse
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Top services by payment revenue</h3>
            </div>
            <div class="card-body">
              @forelse ($topByRevenue as $row)
                @php
                  $pct = $maxRevenue > 0 ? round(($row['revenue'] / $maxRevenue) * 100) : 0;
                @endphp
                <div class="mb-3">
                  <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $row['name'] }}</span>
                    <span>₱{{ number_format($row['revenue'], 2) }}</span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-azure" style="width: {{ $pct }}%" role="progressbar"
                      aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              @empty
                <div class="text-secondary small">No appointment-linked payment revenue for this range.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Catalog &amp; performance</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Service</th>
                <th class="text-end">Bookings</th>
                <th class="text-end">Cancelled</th>
                <th class="text-end">Revenue</th>
                <th class="text-end">Duration</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($serviceRows as $row)
                <tr>
                  <td>
                    <a href="{{ route('admin.services.show', $row['service']->id) }}">{{ $row['name'] }}</a>
                  </td>
                  <td class="text-end">{{ number_format($row['bookings']) }}</td>
                  <td class="text-end">{{ number_format($row['cancellations']) }}</td>
                  <td class="text-end">₱{{ number_format($row['revenue'], 2) }}</td>
                  <td class="text-end">
                    {{ $row['duration_minutes'] !== null ? $row['duration_minutes'].' min' : '—' }}
                  </td>
                  <td>
                    <span class="badge {{ $row['service']->status_badge }}">
                      {{ ucfirst((string) ($row['service']->status ?? 'active')) }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-4">No services match the catalog filter.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
