@extends('patient.layouts.master')

@section('title', 'My payments')

@section('content')
  @php
    $paymentStatusClass = fn (?string $s) => match (strtolower((string) $s)) {
        'paid' => 'text-success',
        'pending' => 'text-warning',
        'partial', 'partially_paid' => 'text-primary',
        'failed', 'cancelled' => 'text-danger',
        'refunded' => 'text-info',
        default => 'text-muted',
    };

    $paymentStatusLabel = fn (?string $s) => match (strtolower((string) $s)) {
        'partially_paid' => 'Partially Paid',
        default => ucfirst(str_replace('_', ' ', (string) ($s ?: 'pending'))),
    };

    $latestPaymentAt = !empty($totals['latest_paid_at']) ? \Illuminate\Support\Carbon::parse($totals['latest_paid_at']) : null;
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> My payments
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-25">
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">Billing history</h3>
                      <p class="mb-0 text-muted font-sm">
                        Track what you already paid, what is still unpaid, and what each payment is for.
                      </p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-sm-6">
                      <div class="dashboard_card orange mb-20">
                        <span><i class="fi-rs-check-circle"></i></span>
                        <h3>₱{{ number_format((float) ($totals['paid_sum'] ?? 0), 2) }}</h3>
                        <p>Total Paid</p>
                      </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <div class="dashboard_card orange mb-20">
                        <span><i class="fi-rs-time-forward"></i></span>
                        <h3>₱{{ number_format((float) ($totals['outstanding_sum'] ?? 0), 2) }}</h3>
                        <p>Unpaid Balance</p>
                      </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <div class="dashboard_card orange mb-20">
                        <span><i class="fi-rs-credit-card"></i></span>
                        <h3>₱{{ number_format((float) ($totals['pending_sum'] ?? 0), 2) }}</h3>
                        <p>Pending Payments</p>
                      </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <div class="dashboard_card orange mb-20">
                        <span><i class="fi-rs-calendar"></i></span>
                        <h3>{{ $latestPaymentAt ? $latestPaymentAt->format('M j, Y') : '—' }}</h3>
                        <p>Latest Payment</p>
                      </div>
                    </div>
                  </div>

                  <div class="card mt-10">
                    <div class="card-header p-0 pb-10 d-flex justify-content-between align-items-center flex-wrap">
                      <h3 class="mb-0">Payments list</h3>
                      <p class="mb-0 text-muted font-sm">{{ (int) ($totals['record_count'] ?? 0) }} record(s)</p>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="order_table table mt-20">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Reference No.</th>
                              <th>Description</th>
                              <th>Related To</th>
                              <th>Amount</th>
                              <th>Method</th>
                              <th>Status</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($payments as $payment)
                              @php
                                $appointmentNo = $payment->appointment?->appointment_no ?? ('#'.$payment->appointment_id);
                                $serviceName = $payment->appointment?->service_name ?? 'Service';
                                $isPaid = (bool) $payment->is_paid;
                                $paidAt = $payment->paid_at;
                                $recordDate = $paidAt ?: $payment->created_at;
                              @endphp
                              <tr>
                                <td>
                                  {{ $recordDate ? $recordDate->timezone(config('app.timezone'))->format('M j, Y g:i A') : '—' }}
                                </td>
                                <td>{{ $payment->reference_no ?: '—' }}</td>
                                <td>
                                  {{ $payment->deposit_notes ?: ('Payment for '.$serviceName.' appointment') }}
                                </td>
                                <td>
                                  <span class="d-block">Appointment: {{ $appointmentNo }}</span>
                                  <span class="d-block text-muted font-xs">Treatment: {{ $serviceName }}</span>
                                </td>
                                <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method ?: '—' }}</td>
                                <td>
                                  <span class="{{ $paymentStatusClass($payment->payment_status) }}">
                                    {{ $paymentStatusLabel($payment->payment_status) }}
                                  </span>
                                  @if ($isPaid && $paidAt)
                                    <span class="d-block text-muted font-xs">Paid {{ $paidAt->format('M j, Y') }}</span>
                                  @endif
                                </td>
                                <td class="text-nowrap">
                                  <a href="{{ route('patient.payments.show', $payment->id) }}" class="btn btn-sm btn-outline-primary mr-5 mb-5">View</a>
                                  <a href="{{ route('patient.payments.show', $payment->id) }}" class="btn btn-sm btn-outline-success mb-5">Receipt</a>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="8" class="text-center text-muted py-40">
                                  No payment records yet. Your invoices and receipts will appear here once payments are created.
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
