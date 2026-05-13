@extends('patient.layouts.master')

@section('title', 'My memberships')

@section('content')
  @php
    $statusClass = fn (?string $s) => match (strtolower((string) $s)) {
        'active' => 'text-success',
        'pending' => 'text-warning',
        'expired' => 'text-muted',
        'cancelled' => 'text-danger',
        'paused', 'suspended' => 'text-primary',
        default => 'text-warning',
    };

    $statusLabel = fn (?string $s) => match (strtolower((string) $s)) {
        'paused' => 'Suspended',
        default => ucfirst((string) ($s ?: 'pending')),
    };

    $paymentStatusClass = fn (?string $s) => match (strtolower((string) $s)) {
        'paid' => 'text-success',
        'partial' => 'text-warning',
        'unpaid', 'overdue' => 'text-danger',
        default => 'text-muted',
    };
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> My memberships
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
                  <div class="card">
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">Membership details</h3>
                      <p class="mb-0 text-muted font-sm">Summary, benefits, usage, payments, history, and terms.</p>
                    </div>
                    @if ($subscription)
                      @php
                        $plan = $subscription->membershipPlan;
                        $services = $plan?->services ?? collect();
                        $totalUsageTarget = (int) (($subscription->sessions_used ?? 0) + ($subscription->sessions_remaining ?? 0));
                        $usageDone = (int) ($subscription->sessions_used ?? 0);
                        $usagePercent = $totalUsageTarget > 0 ? min(100, (int) round(($usageDone / $totalUsageTarget) * 100)) : 0;
                        $memberReference = 'SUB-'.str_pad((string) $subscription->id, 6, '0', STR_PAD_LEFT);
                      @endphp

                      <ul class="nav nav-tabs card-header-tabs mb-20" data-bs-toggle="tabs" role="tablist">
                        <li class="nav-item" role="presentation"><a href="#membership-summary" class="nav-link active" data-bs-toggle="tab" aria-selected="true" role="tab">Summary</a></li>
                        <li class="nav-item" role="presentation"><a href="#membership-benefits" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab">Benefits</a></li>
                        <li class="nav-item" role="presentation"><a href="#membership-usage" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab">Usage</a></li>
                        <li class="nav-item" role="presentation"><a href="#membership-payment" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab">Payment</a></li>
                        <li class="nav-item" role="presentation"><a href="#membership-history" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab">History</a></li>
                        <li class="nav-item" role="presentation"><a href="#membership-terms" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab">Terms & Notes</a></li>
                      </ul>

                      <div class="card-body p-0">
                        <div class="tab-content">
                          <div class="tab-pane active show" id="membership-summary">
                            <div class="p-25">
                              <div class="row">
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Membership plan name</h6>
                                  <p class="mb-0">{{ $plan?->name ?? 'Membership plan' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Status</h6>
                                  <p class="mb-0"><span class="{{ $statusClass($subscription->status) }}">{{ $statusLabel($subscription->status) }}</span></p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Start date</h6>
                                  <p class="mb-0">{{ $subscription->start_date?->format('M j, Y') ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Expiry date</h6>
                                  <p class="mb-0">{{ $subscription->end_date?->format('M j, Y') ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Renewal date</h6>
                                  <p class="mb-0">{{ $subscription->renewal_date?->format('M j, Y') ?? 'Not applicable' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Member ID / reference</h6>
                                  <p class="mb-0">{{ $memberReference }}</p>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="tab-pane" id="membership-benefits">
                            <div class="p-25">
                              <h5 class="mb-10">Benefits included</h5>
                              <ul class="mb-15">
                                @forelse ($services as $service)
                                  <li>{{ $service->name }} @if ((int) ($service->pivot->sessions ?? 0) > 0) ({{ (int) $service->pivot->sessions }} session{{ (int) $service->pivot->sessions > 1 ? 's' : '' }}) @endif</li>
                                @empty
                                  <li>No service benefits configured.</li>
                                @endforelse
                                @if ($plan?->max_usage_per_month)
                                  <li>Max usage per month: {{ $plan->max_usage_per_month }}</li>
                                @endif
                                @if ($plan?->rollover_unused_sessions)
                                  <li>Unused sessions can roll over.</li>
                                @endif
                              </ul>
                              <p class="mb-0 text-muted font-sm">{{ $plan?->description ?: '—' }}</p>
                            </div>
                          </div>

                          <div class="tab-pane" id="membership-usage">
                            <div class="p-25">
                              <h5 class="mb-10">Usage / remaining benefits</h5>
                              <p class="mb-5">Sessions used: <strong>{{ (int) $subscription->sessions_used }}</strong></p>
                              <p class="mb-5">Sessions remaining: <strong>{{ (int) $subscription->sessions_remaining }}</strong></p>
                              <p class="mb-10">{{ (int) $subscription->sessions_used }} of {{ $totalUsageTarget }} sessions used</p>
                              <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $usagePercent }}%;" aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                              </div>
                            </div>
                          </div>

                          <div class="tab-pane" id="membership-payment">
                            <div class="p-25">
                              <h5 class="mb-10">Payment information</h5>
                              <div class="row">
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Plan price</h6>
                                  <p class="mb-0">₱{{ number_format((float) ($plan->price ?? 0), 2) }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Payment status</h6>
                                  <p class="mb-0">
                                    <span class="{{ $paymentStatusClass($latestMembershipPayment?->payment_status) }}">
                                      {{ ucfirst((string) ($latestMembershipPayment?->payment_status ?? 'unpaid')) }}
                                    </span>
                                  </p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Last payment date</h6>
                                  <p class="mb-0">{{ $latestMembershipPayment?->payment_date?->format('M j, Y') ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Next due date</h6>
                                  <p class="mb-0">{{ $subscription->renewal_date?->format('M j, Y') ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Payment method</h6>
                                  <p class="mb-0">{{ $latestMembershipPayment?->method_label ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 mb-15">
                                  <h6 class="text-muted font-sm text-uppercase mb-5">Receipt / invoice</h6>
                                  <p class="mb-0">{{ $latestMembershipPayment?->payment_id ?? ($latestMembershipPayment?->transaction_reference ?? '—') }}</p>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="tab-pane" id="membership-history">
                            <div class="p-25">
                              <h5 class="mb-10">Membership history</h5>
                              <div class="table-responsive">
                                <table class="order_table table m-0">
                                  <thead>
                                    <tr>
                                      <th>Previous membership name</th>
                                      <th>Period covered</th>
                                      <th>Status</th>
                                      <th>Reason ended</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @forelse ($previousMemberships as $old)
                                      <tr>
                                        <td>{{ $old->membershipPlan?->name ?? 'Membership plan' }}</td>
                                        <td>
                                          {{ $old->start_date?->format('M j, Y') ?? '—' }}
                                          —
                                          {{ $old->end_date?->format('M j, Y') ?? 'Present' }}
                                        </td>
                                        <td><span class="{{ $statusClass($old->status) }}">{{ $statusLabel($old->status) }}</span></td>
                                        <td>{{ $old->notes ?: '—' }}</td>
                                      </tr>
                                    @empty
                                      <tr>
                                        <td colspan="4" class="text-center text-muted py-20">No previous memberships found.</td>
                                      </tr>
                                    @endforelse
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          </div>

                          <div class="tab-pane" id="membership-terms">
                            <div class="p-25">
                              <h5 class="mb-10">Terms and notes</h5>
                              <ul class="mb-15">
                                <li><strong>Membership rules:</strong> {{ $plan?->terms_and_conditions ?: '—' }}</li>
                                <li><strong>Validity:</strong> {{ $plan?->duration_label ?: '—' }}</li>
                                <li><strong>Non-transferable reminder:</strong> Membership benefits are intended for the registered account holder only.</li>
                                <li><strong>Booking rules:</strong> Max usage per month {{ $plan?->max_usage_per_month ?: '—' }}.</li>
                                <li><strong>Cancellation rules:</strong> {{ ($plan?->cancellation_allowed ?? false) ? 'Cancellation allowed' : 'Cancellation not allowed' }}.</li>
                                <li><strong>Important clinic notes:</strong> {{ $plan?->internal_notes ?: ($subscription->notes ?: '—') }}</li>
                              </ul>
                              <p class="mb-0"><strong>Aftercare:</strong> {{ $plan?->aftercare ?: '—' }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    @else
                      <div class="card-body p-25">
                        <p class="mb-0">No membership found yet.</p>
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

