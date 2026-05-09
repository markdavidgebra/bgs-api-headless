@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Reports</div>
          <h2 class="page-title">Revenue report</h2>
          <div class="text-secondary small mt-1">Payments from the database; totals use <strong>paid</strong> and <strong>partial</strong> only. Filters apply to the table and summary cards.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.reports') }}" class="btn">Overview</a>
          <a href="{{ route('admin.payments') }}" class="btn btn-primary">Payments</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total revenue (filtered)</div>
              <div class="h2 mb-0">₱{{ number_format($totalRevenue, 2) }}</div>
              <div class="text-secondary small mt-1">Paid + partial</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Today</div>
              <div class="h3 mb-0">₱{{ number_format($dailyRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">This week</div>
              <div class="h3 mb-0">₱{{ number_format($weeklyRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">This month</div>
              <div class="h3 mb-0">₱{{ number_format($monthlyRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Rows</div>
              <div class="h3 mb-0">{{ number_format($rowCount) }}</div>
              <div class="text-secondary small mt-1">All statuses</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">By appointment</div>
              <div class="h3 mb-0">₱{{ number_format($serviceRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">By package</div>
              <div class="h3 mb-0">₱{{ number_format($packageRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">By membership</div>
              <div class="h3 mb-0">₱{{ number_format($subscriptionRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">By product</div>
              <div class="h3 mb-0">₱{{ number_format($productRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Top method</div>
              <div class="h3 mb-0">{{ $topMethodLabel }}</div>
              <div class="text-secondary small mt-1">By amount (paid + partial)</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.revenue') }}">
            <div class="col-lg-3">
              <label class="form-label" for="from">Date range (from)</label>
              <input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="to">Date range (to)</label>
              <input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="method">Payment method</label>
              <select id="method" name="method" class="form-select">
                <option value="">All</option>
                @foreach ($methodOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="type">Reference type</label>
              <select id="type" name="type" class="form-select">
                <option value="">All</option>
                @foreach ($typeOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Payment</th>
                <th>Date</th>
                <th>Patient</th>
                <th>Type</th>
                <th>Reference</th>
                <th class="text-end">Amount</th>
                <th>Method</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($payments as $payment)
                <tr>
                  <td class="font-monospace">
                    <a href="{{ route('admin.payments.show', $payment->id) }}">{{ $payment->payment_id }}</a>
                  </td>
                  <td>
                    {{ $payment->payment_date?->format('M d, Y') ?? '—' }}
                  </td>
                  <td>{{ $payment->patient->name ?? '—' }}</td>
                  <td>{{ $payment->reference_type_label }}</td>
                  <td>{{ $payment->reference_name ?? '—' }}</td>
                  <td class="text-end">₱{{ number_format((float) $payment->amount, 2) }}</td>
                  <td>{{ $payment->method_label }}</td>
                  <td>
                    <span class="badge {{ $payment->status_badge }}">{{ ucfirst((string) $payment->payment_status) }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-secondary py-4">No payments match the selected filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($payments->hasPages())
          <div class="card-footer d-flex justify-content-center">
            {{ $payments->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
