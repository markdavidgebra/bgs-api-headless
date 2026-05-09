@extends('doctor.layouts.master')

@section('title', 'My Services')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> My Services
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')

              <div class="col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20">
                    <h3>My Services</h3>
                    <p class="mb-0">Services currently assigned to your account.</p>
                  </div>

                  <div class="row mb-25">
                    <div class="col-md-4 col-6 mb-15">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-20">
                          <h6 class="text-muted mb-8 font-sm text-uppercase">Assigned</h6>
                          <h4 class="mb-0">{{ number_format($services->count()) }}</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 col-6 mb-15">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-20">
                          <h6 class="text-muted mb-8 font-sm text-uppercase">Active</h6>
                          <h4 class="mb-0">{{ number_format($activeCount) }}</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 col-12 mb-15">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-20">
                          <h6 class="text-muted mb-8 font-sm text-uppercase">Average price</h6>
                          <h4 class="mb-0">₱{{ number_format($avgPrice, 2) }}</h4>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-0">
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Service</th>
                              <th>Summary</th>
                              <th>Duration</th>
                              <th class="text-end">Price</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($services as $service)
                              <tr>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->summary_text }}</td>
                                <td>{{ $service->duration_label }}</td>
                                <td class="text-end">
                                  @if ($service->promo_price !== null)
                                    <span class="text-secondary text-decoration-line-through small d-block">₱{{ number_format((float) $service->price, 2) }}</span>
                                    <span class="fw-medium">₱{{ number_format((float) $service->promo_price, 2) }}</span>
                                  @else
                                    ₱{{ number_format((float) $service->price, 2) }}
                                  @endif
                                </td>
                                <td>
                                  <span class="badge {{ $service->status_badge }}">{{ ucfirst($service->status_label) }}</span>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-secondary py-4">No services are currently assigned to you.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
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
