@extends('clinical-staff.layouts.master')

@section('title', 'My Services')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> My Services
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('clinical-staff.layouts.sidebar')

              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20">
                    <h3>Services</h3>
                    <p class="mb-0">All the services</p>
                  </div>

                  <div class="row mb-25">
                    <div class="col-md-6 col-6 mb-15">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-20">
                          <h6 class="text-muted mb-8 font-sm text-uppercase">Total</h6>
                          <h4 class="mb-0">{{ number_format($services->count()) }}</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-6 mb-15">
                      <div class="card mb-0 h-100">
                        <div class="card-body p-20">
                          <h6 class="text-muted mb-8 font-sm text-uppercase">Active</h6>
                          <h4 class="mb-0">{{ number_format($activeCount) }}</h4>
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
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($services as $service)
                              <tr>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->summary_text }}</td>
                                <td>{{ $service->duration_label }}</td>
                                <td>
                                  <span class="badge {{ $service->status_badge }}">{{ ucfirst($service->status_label) }}</span>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="4" class="text-center text-secondary py-4">No services found.</td>
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
