@extends('patient.layouts.master')

@section('title', 'Payment details')

@section('content')
  @php
    $statusClass = fn (?string $s) => match (strtolower((string) $s)) {
        'paid' => 'text-success',
        'pending' => 'text-warning',
        'partial', 'partially_paid' => 'text-primary',
        'failed', 'cancelled' => 'text-danger',
        'refunded' => 'text-info',
        default => 'text-muted',
    };

    $statusLabel = fn (?string $s) => match (strtolower((string) $s)) {
        'partially_paid' => 'Partially Paid',
        default => ucfirst(str_replace('_', ' ', (string) ($s ?: 'pending'))),
    };

    $appointment = $payment->appointment;
    $recordDate = $payment->paid_at ?: $payment->created_at;
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> <a href="{{ route('patient.payments') }}">My payments</a> <span></span> Payment details
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
                  <div class="card mb-20">
                    <div class="card-header p-0 pb-10 d-flex align-items-center justify-content-between flex-wrap">
                      <h3 class="mb-0">Payment details</h3>
                      <div>
                        <a href="{{ route('patient.payments') }}" class="btn btn-sm btn-outline-secondary mr-5">Back</a>
                        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-primary">Print Receipt</button>
                      </div>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-body">
                      <div class="row mb-15">
                        <div class="col-md-6">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Reference No.</h6>
                          <p class="mb-0">{{ $payment->reference_no ?: '—' }}</p>
                        </div>
                        <div class="col-md-6">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Invoice No.</h6>
                          <p class="mb-0">{{ $payment->invoice_no ?: '—' }}</p>
                        </div>
                      </div>

                      <div class="row mb-15">
                        <div class="col-md-6">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Date</h6>
                          <p class="mb-0">{{ $recordDate ? $recordDate->timezone(config('app.timezone'))->format('M j, Y g:i A') : '—' }}</p>
                        </div>
                        <div class="col-md-6">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Status</h6>
                          <p class="mb-0"><span class="{{ $statusClass($payment->payment_status) }}">{{ $statusLabel($payment->payment_status) }}</span></p>
                        </div>
                      </div>

                      <div class="row mb-15">
                        <div class="col-md-6">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Amount</h6>
                          <p class="mb-0">₱{{ number_format((float) $payment->amount, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Method</h6>
                          <p class="mb-0">{{ $payment->payment_method ?: '—' }}</p>
                        </div>
                      </div>

                      <hr>

                      <div class="row mb-15">
                        <div class="col-md-12">
                          <h5 class="mb-10">Related To</h5>
                        </div>
                        <div class="col-md-4">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Appointment</h6>
                          <p class="mb-0">{{ $appointment?->appointment_no ?? ('#'.$payment->appointment_id) }}</p>
                        </div>
                        <div class="col-md-4">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Treatment / Service</h6>
                          <p class="mb-0">{{ $appointment?->service_name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Clinical staff</h6>
                          <p class="mb-0">{{ $appointment?->doctor_name ?? '—' }}</p>
                        </div>
                      </div>

                      <div class="row mb-10">
                        <div class="col-md-12">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Description / Notes</h6>
                          <p class="mb-0">{{ $payment->deposit_notes ?: ('Payment for '.$appointment?->service_name.' appointment.') }}</p>
                        </div>
                      </div>
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
