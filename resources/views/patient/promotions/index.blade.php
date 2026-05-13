@extends('patient.layouts.master')

@section('title', 'Promotions')

@section('content')
  @php
    $statusClass = fn (?string $status) => match (strtolower((string) $status)) {
        'active' => 'text-success',
        'scheduled' => 'text-primary',
        'expired', 'inactive' => 'text-danger',
        default => 'text-warning',
    };

    $statusLabel = fn (?string $status) => ucfirst((string) ($status ?: 'draft'));
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> Promotions
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
                      <h3 class="mb-0">Promotions & offers</h3>
                      <p class="mb-0 text-muted font-sm">
                        See current promos, discount values, who can use them, validity dates, and how to avail.
                      </p>
                    </div>
                  </div>

                  @if ($featuredPromo)
                    <div class="card mb-25 border-0" style="background: linear-gradient(135deg, #0d6efd, #5b8cff);">
                      <div class="card-body p-25 text-white">
                        <span class="badge bg-light text-dark mb-10">Featured Promo</span>
                        <h3 class="text-white mb-10">{{ $featuredPromo->name }}</h3>
                        <p class="mb-10">{{ $featuredPromo->description ?: 'Special offer for patients.' }}</p>
                        <div class="d-flex flex-wrap align-items-center mb-15">
                          <span class="badge bg-warning text-dark mr-10 mb-5">{{ $featuredPromo->discount_label }} OFF</span>
                          @if ($featuredPromo->end_date)
                            <span class="mb-5">Valid until {{ $featuredPromo->end_date->format('M j, Y') }}</span>
                          @else
                            <span class="mb-5">No expiry date set</span>
                          @endif
                        </div>
                        <a href="{{ route('patient.appointments.book') }}?promo={{ urlencode((string) $featuredPromo->code) }}"
                          class="btn btn-sm btn-light mr-5">Book Now</a>
                        <a href="{{ route('patient.promotions.show', $featuredPromo->id) }}"
                          class="btn btn-sm btn-outline-light">View Details</a>
                      </div>
                    </div>
                  @endif

                  <div class="row">
                    @forelse ($promotions as $promotion)
                      <div class="col-lg-6 mb-20">
                        <div class="card h-100">
                          @if ($promotion->image_url)
                            <img src="{{ $promotion->image_url }}" alt="{{ $promotion->name }}" class="card-img-top"
                              style="height: 180px; object-fit: cover;">
                          @endif
                          <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-10">
                              <h5 class="mb-0">{{ $promotion->name }}</h5>
                              <span class="{{ $statusClass($promotion->status) }}">{{ $statusLabel($promotion->status) }}</span>
                            </div>

                            <p class="text-muted mb-10">{{ $promotion->description ?: 'No description provided.' }}</p>

                            <p class="mb-5"><strong>Discount:</strong> {{ $promotion->discount_label }}</p>
                            <p class="mb-5"><strong>Who can use:</strong> {{ $promotion->new_patients_only ? 'New patients only' : 'All patients' }}</p>
                            <p class="mb-5"><strong>Valid:</strong> {{ $promotion->validity_label ?: 'Always available' }}</p>
                            <p class="mb-10"><strong>Applies to:</strong> {{ $promotion->scope_label }}</p>

                            @if ($promotion->display_note)
                              <p class="mb-10 text-muted font-sm">{{ $promotion->display_note }}</p>
                            @endif

                            <div class="d-flex flex-wrap">
                              <a href="{{ route('patient.promotions.show', $promotion->id) }}"
                                class="btn btn-sm btn-outline-primary mr-5 mb-5">View Details</a>
                              <a href="{{ route('patient.appointments.book') }}?promo={{ urlencode((string) $promotion->code) }}"
                                class="btn btn-sm btn-primary mb-5">Avail Promo</a>
                            </div>
                          </div>
                        </div>
                      </div>
                    @empty
                      <div class="col-12">
                        <div class="card">
                          <div class="card-body text-center text-muted py-40">
                            No active promotions right now. Please check back soon.
                          </div>
                        </div>
                      </div>
                    @endforelse
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
