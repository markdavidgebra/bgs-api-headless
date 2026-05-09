@extends('patient.layouts.master')

@section('title', 'Promotion details')

@section('content')
  @php
    /** @var \App\Models\Promotion $promotion */
    $statusClass = fn (?string $status) => match (strtolower((string) $status)) {
        'active' => 'text-success',
        'scheduled' => 'text-primary',
        'expired', 'inactive' => 'text-danger',
        default => 'text-warning',
    };
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> <a href="{{ route('patient.promotions') }}">Promotions</a> <span></span> Details
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-20">
                    @if ($promotion->image_url)
                      <img src="{{ $promotion->image_url }}" alt="{{ $promotion->name }}" class="card-img-top"
                        style="max-height: 320px; object-fit: cover;">
                    @endif

                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start flex-wrap mb-10">
                        <h3 class="mb-5">{{ $promotion->name }}</h3>
                        <span class="{{ $statusClass($promotion->status) }}">{{ ucfirst((string) $promotion->status) }}</span>
                      </div>

                      <p class="mb-10 text-muted">{{ $promotion->description ?: 'No description available.' }}</p>

                      <div class="row mb-10">
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Discount / Offer</h6>
                          <p class="mb-0">{{ $promotion->discount_label }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Who Can Use</h6>
                          <p class="mb-0">{{ $promotion->new_patients_only ? 'New patients only' : 'All patients' }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Validity</h6>
                          <p class="mb-0">{{ $promotion->validity_label ?: 'Always available' }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Promo Code</h6>
                          <p class="mb-0">{{ $promotion->code ?: 'No code required' }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Applies To</h6>
                          <p class="mb-0">{{ $promotion->scope_label }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Per Patient Limit</h6>
                          <p class="mb-0">{{ $promotion->limit_per_patient ?: 'No limit set' }}</p>
                        </div>
                      </div>

                      @if ($promotion->display_note || $promotion->terms_and_conditions)
                        <hr>
                        <h5 class="mb-10">How to avail</h5>
                        <ol class="mb-10">
                          <li>Click the Avail Promo button below.</li>
                          <li>Book your appointment and include promo code: <strong>{{ $promotion->code ?: 'N/A' }}</strong>.</li>
                          <li>Confirm with the clinic staff during payment.</li>
                        </ol>
                        @if ($promotion->display_note)
                          <p class="mb-10"><strong>Note:</strong> {{ $promotion->display_note }}</p>
                        @endif
                        @if ($promotion->terms_and_conditions)
                          <p class="mb-0 text-muted font-sm">{{ $promotion->terms_and_conditions }}</p>
                        @endif
                      @endif
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header p-0 pb-10">
                      <h4 class="mb-0">Included Items</h4>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-10">
                          <h6 class="mb-5">Services</h6>
                          <p class="mb-0">
                            @if ($promotion->services->isNotEmpty())
                              {{ $promotion->services->pluck('name')->implode(', ') }}
                            @else
                              —
                            @endif
                          </p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="mb-5">Treatment Packages</h6>
                          <p class="mb-0">
                            @if ($promotion->treatmentPackages->isNotEmpty())
                              {{ $promotion->treatmentPackages->pluck('name')->implode(', ') }}
                            @else
                              —
                            @endif
                          </p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="mb-5">Membership Plans</h6>
                          <p class="mb-0">
                            @if ($promotion->membershipPlans->isNotEmpty())
                              {{ $promotion->membershipPlans->pluck('name')->implode(', ') }}
                            @else
                              —
                            @endif
                          </p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="mb-5">Products</h6>
                          <p class="mb-0">
                            @if ($promotion->products->isNotEmpty())
                              {{ $promotion->products->pluck('name')->implode(', ') }}
                            @else
                              —
                            @endif
                          </p>
                        </div>
                      </div>

                      <div class="mt-10">
                        <a href="{{ route('appointment') }}?promo={{ urlencode((string) $promotion->code) }}"
                          class="btn btn-primary mr-5">Avail Promo</a>
                        <a href="{{ route('patient.promotions') }}" class="btn btn-outline-secondary">Back to Promotions</a>
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
