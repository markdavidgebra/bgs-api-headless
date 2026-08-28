@extends('clinical-staff.layouts.master')

@section('title', 'Clinic product inventory')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Product inventory
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
                    <h3>Clinic product inventory</h3>
                    <p class="mb-0">Active catalog items and current on-hand quantities (read-only).</p>
                  </div>

                  @if (! empty($doctorProductInventorySummary))
                    @php($inv = $doctorProductInventorySummary)
                    <div class="row mb-25">
                      <div class="col-md-3 col-6 mb-15">
                        <div class="card mb-0 h-100">
                          <div class="card-body p-20">
                            <h6 class="text-muted mb-8 font-sm text-uppercase">SKUs (active)</h6>
                            <h4 class="mb-0">{{ number_format($inv['sku_count']) }}</h4>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-3 col-6 mb-15">
                        <div class="card mb-0 h-100">
                          <div class="card-body p-20">
                            <h6 class="text-muted mb-8 font-sm text-uppercase">Total units</h6>
                            <h4 class="mb-0">{{ number_format($inv['total_units']) }}</h4>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-3 col-6 mb-15">
                        <div class="card mb-0 h-100">
                          <div class="card-body p-20">
                            <h6 class="text-muted mb-8 font-sm text-uppercase">Low stock</h6>
                            <h4 class="mb-0">{{ number_format($inv['low_stock']) }}</h4>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-3 col-6 mb-15">
                        <div class="card mb-0 h-100">
                          <div class="card-body p-20">
                            <h6 class="text-muted mb-8 font-sm text-uppercase">Out of stock</h6>
                            <h4 class="mb-0">{{ number_format($inv['out_of_stock']) }}</h4>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif

                  <div class="card mb-0">
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Product</th>
                              <th>Category</th>
                              <th>SKU</th>
                              <th class="text-end">On hand</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($products as $product)
                              <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category ?? '—' }}</td>
                                <td class="font-monospace">{{ $product->sku ?? '—' }}</td>
                                <td class="text-end">{{ number_format((int) $product->stock_quantity) }}</td>
                                <td>
                                  @php($label = match ($product->stock_status) {
                                      'out_of_stock' => 'Out of stock',
                                      'low_stock' => 'Low stock',
                                      default => 'In stock',
                                  })
                                  <span class="badge {{ $product->stock_status_badge }}">{{ $label }}</span>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-secondary py-4">No active products in the catalog.</td>
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
