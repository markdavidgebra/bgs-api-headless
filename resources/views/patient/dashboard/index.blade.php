@extends('patient.layouts.master')

@section('title', 'Patient dashboard')

@section('content')
  @php
    $statusClass = fn (?string $s) => match ($s) {
        'completed' => 'text-success',
        'cancelled' => 'text-danger',
        'confirmed' => 'text-primary',
        'rescheduled' => 'text-info',
        default => 'text-warning',
    };
  @endphp
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> Dashboard
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
                  <div class="section-title">
                    <h3>Hello, {{ $patient->name ?? 'there' }}!</h3>
                    <p class="mb-15">
                      Here’s a quick snapshot of your care: next visit, treatments, packages, membership, and the latest
                      payment — plus shortcuts to book and review your records.
                    </p>
                    <div class="d-flex flex-wrap mt-15">
                      <a href="{{ route('patient.appointments.book') }}" class="btn btn-sm mr-10 mb-10">
                        <i class="fi-rs-calendar mr-5"></i>Book appointment
                      </a>
                      <a href="{{ route('patient.appointments') }}" class="btn btn-sm btn-outline-primary mr-10 mb-10">
                        <i class="fi-rs-list mr-5"></i>View appointments
                      </a>
                      <a href="{{ route('patient.payments') }}" class="btn btn-sm btn-outline-primary mb-10">
                        <i class="fi-rs-bank mr-5"></i>View payments
                      </a>
                    </div>
                  </div>

                  <div class="row mt-30">
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Upcoming appointment</h6>
                          @if ($upcomingAppointment)
                            <p class="font-lg mb-5">
                              {{ $upcomingAppointment->date_display }}
                              <span class="text-muted font-xs">&middot; {{ $upcomingAppointment->time_display }}</span>
                            </p>
                            <p class="mb-5">{{ $upcomingAppointment->service_name }}</p>
                            <p class="font-sm text-muted mb-10">{{ $upcomingAppointment->clinical_staff_name }}</p>
                            <span class="{{ $statusClass($upcomingAppointment->status) }} font-sm">{{ $upcomingAppointment->status_label }}</span>
                            <p class="mt-15 mb-0 font-xs text-muted">
                              Ref. {{ $upcomingAppointment->appointment_no ?? '#' . $upcomingAppointment->id }}
                            </p>
                          @else
                            <p class="mb-15">You don’t have a scheduled visit yet.</p>
                            <a href="{{ route('appointment') }}" class="font-sm">Book an appointment</a>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Next treatment</h6>
                          @if ($upcomingAppointment)
                            <p class="font-lg mb-10">{{ $upcomingAppointment->service_name }}</p>
                            <p class="font-sm text-muted mb-0">
                              Scheduled for {{ $upcomingAppointment->date_display }} at {{ $upcomingAppointment->time_display }}
                              with {{ $upcomingAppointment->clinical_staff_name }}.
                            </p>
                          @else
                            <p class="mb-15">No treatment is on the calendar yet.</p>
                            <a href="{{ route('appointment') }}" class="font-sm">Schedule a treatment</a>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Active package</h6>
                          @if ($activePackage)
                            <p class="font-lg mb-5">{{ $activePackage->treatmentPackage?->name ?? 'Treatment package' }}</p>
                            <p class="font-sm mb-5">
                              <strong>{{ (int) $activePackage->remaining_sessions }}</strong> of
                              <strong>{{ (int) $activePackage->total_sessions }}</strong> sessions remaining
                            </p>
                            @if ($activePackage->end_date)
                              <p class="font-sm text-muted mb-10">Valid through {{ $activePackage->end_date->format('M j, Y') }}</p>
                            @endif
                            <span class="font-sm {{ $activePackage->status === 'active' ? 'text-success' : 'text-muted' }}">
                              {{ ucfirst($activePackage->status) }}
                            </span>
                            <p class="mt-15 mb-0">
                              <a href="{{ route('patient.packages') }}" class="font-sm">Package details</a>
                            </p>
                          @else
                            <p class="mb-15">You have no active treatment package.</p>
                            <a href="{{ route('patient.packages') }}" class="font-sm">Browse packages</a>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Active membership</h6>
                          @if ($activeMembership)
                            <p class="font-lg mb-5">{{ $activeMembership->membershipPlan?->name ?? 'Membership' }}</p>
                            @if ($activeMembership->end_date)
                              <p class="font-sm text-muted mb-5">Renews / ends {{ $activeMembership->end_date->format('M j, Y') }}</p>
                            @endif
                            @if ($activeMembership->renewal_date)
                              <p class="font-sm text-muted mb-10">Next renewal {{ $activeMembership->renewal_date->format('M j, Y') }}</p>
                            @endif
                            <p class="font-sm mb-10">
                              Sessions left: <strong>{{ (int) $activeMembership->sessions_remaining }}</strong>
                            </p>
                            <span class="font-sm text-success">{{ ucfirst($activeMembership->status) }}</span>
                            <p class="mt-15 mb-0">
                              <a href="{{ route('patient.memberships') }}" class="font-sm">Membership details</a>
                            </p>
                          @else
                            <p class="mb-15">You don’t have an active membership.</p>
                            <a href="{{ route('patient.memberships') }}" class="font-sm">View memberships</a>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-25">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-25">
                          <h6 class="text-muted mb-10 font-sm text-uppercase">Latest payment status</h6>
                          @if ($latestPaymentRecord && $latestPaymentKind === 'payment')
                            @php($lp = $latestPaymentRecord)
                            <p class="font-lg mb-5">{{ $lp->formatted_amount }}</p>
                            <p class="font-sm mb-5">
                              {{ $lp->reference_type_label }}
                              @if ($lp->reference_name)
                                — {{ $lp->reference_name }}
                              @endif
                            </p>
                            <p class="font-sm mb-10">
                              <span class="{{ match ($lp->payment_status) { 'paid' => 'text-success', 'unpaid' => 'text-danger', 'partial' => 'text-warning', default => 'text-muted' } }}">
                                {{ ucfirst($lp->payment_status ?? 'unknown') }}
                              </span>
                              <span class="text-muted">&middot; {{ $lp->method_label }}</span>
                            </p>
                            @if ($lp->payment_date)
                              <p class="font-xs text-muted mb-0">{{ $lp->payment_date->format('M j, Y') }}</p>
                            @endif
                          @elseif ($latestPaymentRecord && $latestPaymentKind === 'appointment_payment')
                            @php($ip = $latestPaymentRecord)
                            <p class="font-lg mb-5">₱{{ number_format((float) $ip->amount, 2) }}</p>
                            <p class="font-sm mb-5">Invoice {{ $ip->invoice_no }}</p>
                            <p class="font-sm mb-10">
                              @if ($ip->is_paid)
                                <span class="text-success">Paid</span>
                              @else
                                <span class="{{ match ($ip->payment_status) { 'paid' => 'text-success', default => 'text-warning' } }}">
                                  {{ ucfirst($ip->payment_status) }}
                                </span>
                              @endif
                            </p>
                            <p class="font-xs text-muted mb-0">
                              {{ ($ip->paid_at ?? $ip->created_at)?->format('M j, Y') }}
                            </p>
                          @else
                            <p class="mb-15">No payments recorded yet.</p>
                          @endif
                          <p class="mt-15 mb-0">
                            <a href="{{ route('patient.payments') }}" class="font-sm">Full payment history</a>
                          </p>
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
