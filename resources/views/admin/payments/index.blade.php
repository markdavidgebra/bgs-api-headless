@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $payments */
    $hasFilters =
        request()->filled('search') ||
        request()->filled('payment_status') ||
        request()->filled('payment_method') ||
        request()->filled('date') ||
        request()->filled('reference_type');
    $statusOptions = ['paid', 'unpaid', 'partial', 'refunded', 'cancelled'];
    $methodOptions = [
        'cash' => 'Cash',
        'gcash' => 'GCash',
        'maya' => 'Maya',
        'card' => 'Card',
        'bank_transfer' => 'Bank transfer',
    ];
    $referenceTypeOptions = [
        'appointment' => 'Appointment',
        'service' => 'Service',
        'package' => 'Package',
        'membership' => 'Membership',
        'product' => 'Product',
    ];
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Finance</div>
          <h2 class="page-title">Payments</h2>
          <div class="text-secondary small mt-1">Transactions from the payments table (patient = user).</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a class="btn" data-bs-toggle="collapse" href="#payment-filters" role="button"
            aria-expanded="{{ $hasFilters ? 'true' : 'false' }}" aria-controls="payment-filters">Filters</a>
          <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">Add payment</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total revenue</div>
              <div class="h2 mb-0">₱{{ number_format($totalRevenue, 2) }}</div>
              <div class="text-secondary small mt-1">Paid + partial (all time)</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Today (paid + partial)</div>
              <div class="h2 mb-0">₱{{ number_format($todaysRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Pending</div>
              <div class="h2 mb-0 text-yellow">{{ number_format($pendingPayments) }}</div>
              <div class="text-secondary small mt-1">Unpaid / cancelled</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Partial</div>
              <div class="h2 mb-0">{{ number_format($partialPayments) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Refunded</div>
              <div class="h2 mb-0">{{ number_format($refundedPayments) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Membership revenue</div>
              <div class="h3 mb-0">₱{{ number_format($membershipRevenue, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Package revenue</div>
              <div class="h3 mb-0">₱{{ number_format($packageRevenue, 2) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body border-bottom py-3">
          <form class="row g-3 align-items-end collapse {{ $hasFilters ? 'show' : '' }}" id="payment-filters" method="GET"
            action="{{ route('admin.payments') }}">
            <div class="col-lg-3">
              <label class="form-label" for="search">Search</label>
              <input id="search" type="text" class="form-control" name="search" value="{{ request('search') }}"
                placeholder="Patient, payment ID, transaction ref…">
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="payment_status">Status</label>
              <select id="payment_status" class="form-select" name="payment_status">
                <option value="">All</option>
                @foreach ($statusOptions as $st)
                  <option value="{{ $st }}" @selected(request('payment_status') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="payment_method">Method</label>
              <select id="payment_method" class="form-select" name="payment_method">
                <option value="">All</option>
                @foreach ($methodOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="date">Payment date</label>
              <input id="date" type="date" class="form-control" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="reference_type">Reference</label>
              <select id="reference_type" class="form-select" name="reference_type">
                <option value="">All</option>
                @foreach ($referenceTypeOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('reference_type') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-1 d-grid">
              <label class="form-label d-none d-lg-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>

            @if ($hasFilters)
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.payments') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary small fw-bold">Payment ID</th>
                <th class="text-uppercase text-secondary small fw-bold">Patient</th>
                <th class="text-uppercase text-secondary small fw-bold">Item</th>
                <th class="text-end text-uppercase text-secondary small fw-bold">Amount</th>
                <th class="text-uppercase text-secondary small fw-bold">Method</th>
                <th class="text-uppercase text-secondary small fw-bold">Status</th>
                <th class="text-uppercase text-secondary small fw-bold">Date</th>
                <th class="w-1 text-uppercase text-secondary small fw-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($payments as $payment)
                <tr>
                  <td class="font-monospace text-secondary small">{{ $payment->payment_id }}</td>
                  <td>
                    <div class="fw-medium">{{ $payment->patient->name ?? '—' }}</div>
                    @if ($payment->patient?->email)
                      <div class="text-secondary small">{{ $payment->patient->email }}</div>
                    @endif
                  </td>
                  <td class="text-secondary">{{ $payment->reference_name ?? '—' }}</td>
                  <td class="text-end font-monospace">{{ $payment->formatted_amount }}</td>
                  <td>{{ $payment->method_label }}</td>
                  <td>
                    <span class="badge {{ $payment->status_badge }}">{{ ucfirst((string) $payment->payment_status) }}</span>
                  </td>
                  <td class="text-secondary small">
                    {{ $payment->payment_date?->format('M j, Y') ?? '—' }}</td>
                  <td>
                    <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-primary">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-secondary py-4">No payments found. Add a payment or adjust filters.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-secondary small">Revenue totals use status paid and partial only.</div>
          <div>{{ $payments->links() }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
