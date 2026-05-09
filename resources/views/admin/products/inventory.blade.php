@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Products</div>
          <h2 class="page-title">Inventory monitoring</h2>
          <div class="text-secondary small mt-1">Live overview of on-hand quantity versus minimum thresholds.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#inventory-filters" role="button"
              aria-expanded="{{ request()->filled('search') || request()->filled('stock_status') ? 'true' : 'false' }}"
              aria-controls="inventory-filters">Filters</a>
            <a href="{{ route('admin.products') }}" class="btn">Back to catalog</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">SKUs tracked</div>
              <div class="h2 mb-0">{{ number_format($totalSkus) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">In stock</div>
              <div class="h2 mb-0 text-green">{{ number_format($inStockCount) }}</div>
              <div class="text-secondary small mt-1">Above minimum level</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Low stock alerts</div>
              <div class="h2 mb-0 text-yellow">{{ number_format($lowStockCount) }}</div>
              <div class="text-secondary small mt-1">At or below minimum</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Out of stock</div>
              <div class="h2 mb-0 text-red">{{ number_format($outStockCount) }}</div>
              <div class="text-secondary small mt-1">Zero on hand</div>
            </div>
          </div>
        </div>
      </div>

      @if ($lowStockCount > 0 || $outStockCount > 0)
        <div class="row g-3 mb-3">
          @if ($outStockCount > 0)
            <div class="col-lg-6">
              <div class="alert alert-important alert-danger mb-0" role="alert">
                <div class="d-flex">
                  <div>
                    <h4 class="alert-title">Out of stock</h4>
                    <div class="text-secondary">{{ $outStockCount }} {{ $outStockCount === 1 ? 'product needs' : 'products need' }} replenishment.</div>
                    <ul class="mb-0 mt-2">
                      @foreach ($outStockProducts as $p)
                        <li>
                          <span class="fw-medium">{{ $p->name }}</span>
                          @if ($p->sku)
                            <span class="text-secondary">({{ $p->sku }})</span>
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          @endif
          @if ($lowStockCount > 0)
            <div class="col-lg-6">
              <div class="alert alert-important alert-warning mb-0" role="alert">
                <div class="d-flex">
                  <div>
                    <h4 class="alert-title">Low stock</h4>
                    <div class="text-secondary">Current quantity is at or below the minimum stock level.</div>
                    <ul class="mb-0 mt-2">
                      @foreach ($lowStockProducts as $p)
                        <li>
                          <span class="fw-medium">{{ $p->name }}</span>
                          <span class="text-secondary">— {{ number_format((int) $p->stock_quantity) }}
                            {{ $p->unit ?? 'pcs' }} (min {{ number_format((int) $p->minimum_stock_alert) }})</span>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      @endif

      <div class="card">
        <div class="card-body border-bottom py-3">
          <form
            class="row g-3 align-items-end collapse @if (request()->filled('search') || request()->filled('stock_status')) show @endif"
            id="inventory-filters" method="GET" action="{{ route('admin.products.inventory') }}">
            <div class="col-lg-5">
              <label class="form-label" for="inv-search">Search</label>
              <input id="inv-search" type="text" class="form-control" name="search" placeholder="Product, SKU, or brand…"
                value="{{ request('search') }}">
            </div>
            <div class="col-lg-4">
              <label class="form-label" for="inv-status">Stock status</label>
              <select id="inv-status" class="form-select" name="stock_status">
                <option value="">All</option>
                <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In stock</option>
                <option value="low_stock" @selected(request('stock_status') === 'low_stock')>Low stock</option>
                <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of stock</option>
              </select>
            </div>
            <div class="col-lg-2 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
            @if (request()->filled('search') || request()->filled('stock_status'))
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.products.inventory') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div id="inventory-table" class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead>
              <tr>
                <th>
                  <button type="button"
                    class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-product">Product</button>
                </th>
                <th>
                  <button type="button"
                    class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-sku">SKU</button>
                </th>
                <th class="text-end">
                  <button type="button"
                    class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-current">Current stock</button>
                </th>
                <th class="text-end">
                  <button type="button"
                    class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-min">Minimum stock</button>
                </th>
                <th>
                  <button type="button"
                    class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-status">Stock status</button>
                </th>
                <th class="w-1 text-uppercase text-secondary small fw-bold">Action</th>
              </tr>
            </thead>
            <tbody class="table-tbody">
              @forelse ($products as $product)
                @php
                  [$statusLabel, $statusBadge] = match ($product->stock_status) {
                      'out_of_stock' => ['Out of stock', 'bg-red-lt'],
                      'low_stock' => ['Low stock', 'bg-yellow-lt'],
                      default => ['In stock', 'bg-green-lt'],
                  };
                  $unit = $product->unit ?? 'pcs';
                @endphp
                <tr>
                  <td class="sort-product">
                    <div class="fw-medium">{{ $product->name }}</div>
                  </td>
                  <td class="sort-sku font-monospace text-secondary">{{ $product->sku ?? '—' }}</td>
                  <td class="text-end sort-current" data-current="{{ (int) $product->stock_quantity }}">
                    {{ number_format((int) $product->stock_quantity) }} <span class="text-secondary small">{{ $unit }}</span>
                  </td>
                  <td class="text-end sort-min" data-min="{{ (int) $product->minimum_stock_alert }}">
                    {{ number_format((int) $product->minimum_stock_alert) }} <span class="text-secondary small">{{ $unit }}</span>
                  </td>
                  <td class="sort-status" data-status="{{ $statusLabel }}">
                    <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                  </td>
                  <td>
                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-primary">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-5">
                    @if (request()->filled('search') || request()->filled('stock_status'))
                      No products match the current filters.
                    @else
                      No products in the catalog yet.
                    @endif
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-secondary small">Adjust quantities on each product’s edit screen (logs stock movements).</div>
          <div class="text-secondary small">Click column headers to sort the current list.</div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('admin/assets/dist/libs/list.js/dist/list.min.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var el = document.getElementById('inventory-table');
      if (!el || !window.List) return;
      new List('inventory-table', {
        sortClass: 'table-sort',
        listClass: 'table-tbody',
        valueNames: [
          'sort-product',
          'sort-sku',
          { attr: 'data-current', name: 'sort-current' },
          { attr: 'data-min', name: 'sort-min' },
          { attr: 'data-status', name: 'sort-status' },
        ]
      });
    });
  </script>
@endpush
