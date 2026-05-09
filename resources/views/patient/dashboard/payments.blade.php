@extends('frontend.layouts.dashboard.patient.patients_dashboard')

@section('title', 'My payments')

@section('content')
  @php
    $paymentStatusClass = fn (?string $s) => match ($s) {
        'paid' => 'text-success',
        'refunded', 'partially_refunded' => 'text-info',
        'failed', 'void' => 'text-danger',
        default => 'text-warning',
    };
  @endphp
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> Payments
        </div>
      </div>
    </div>
    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.partials.portal-sidebar')
              <div class="col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-4">
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">Payments &amp; invoices</h3>
                    </div>
                    <div class="card-body p-0">
                      <p class="mb-0">
                        Invoices tied to your appointments appear below. Paid amounts reflect successful
                        payments; outstanding balances may still be due according to your treatment plan.
                      </p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-4 col-sm-6">
                      <div class="dashboard_card blue">
                        <span><i class="fi-rs-receipt"></i></span>
                        <h3>{{ $totals['record_count'] }}</h3>
                        <p>Invoices</p>
                      </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                      <div class="dashboard_card green">
                        <span><i class="fi-rs-check"></i></span>
                        <h3>₱{{ number_format((float) $totals['paid_sum'], 2) }}</h3>
                        <p>Paid total</p>
                      </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                      <div class="dashboard_card orange">
                        <span><i class="fi-rs-time-forward"></i></span>
                        <h3>₱{{ number_format((float) $totals['outstanding_sum'], 2) }}</h3>
                        <p>Outstanding</p>
                      </div>
                    </div>
                  </div>

                  <div class="card mt-30">
                    <div class="card-header p-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <h3 class="mb-0">Payment history</h3>
                      <a href="{{ route('patient.appointments') }}" class="font-sm">View appointments</a>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="order_table table m-0 mt-20">
                          <thead>
                            <tr>
                              <th>Invoice</th>
                              <th>Appointment</th>
                              <th>Service</th>
                              <th>Amount</th>
                              <th>Method</th>
                              <th>Status</th>
                              <th>Paid</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($payments as $payment)
                              <tr>
                                <td>{{ $payment->invoice_no }}</td>
                                <td>
                                  {{ $payment->appointment?->appointment_no ?? ('#' . $payment->appointment_id) }}
                                  <span class="d-block font-xs text-muted">
                                    {{ $payment->appointment?->date_display ?? '—' }}
                                  </span>
                                </td>
                                <td>{{ $payment->appointment?->service_name ?? '—' }}</td>
                                <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method ?? '—' }}</td>
                                <td>
                                  <span class="{{ $paymentStatusClass($payment->payment_status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_status ?? 'pending')) }}
                                  </span>
                                </td>
                                <td>
                                  @if ($payment->is_paid && $payment->paid_at)
                                    {{ $payment->paid_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                                  @elseif ($payment->is_paid)
                                    <span class="text-muted">—</span>
                                  @else
                                    <span class="text-muted">Pending</span>
                                  @endif
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="7" class="text-center text-muted py-40">
                                  No payment records yet. When you book and pay for a visit, invoices will show here.
                                  <a href="{{ route('appointment') }}">Book an appointment</a>.
                                </td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      @if ($payments->hasPages())
                        <div class="pagination-area mt-20 mb-20">
                          {{ $payments->links() }}
                        </div>
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
  </main>
@endsection
