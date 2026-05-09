@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \Illuminate\Support\Collection<int, \App\Models\StockMovement> $movements */
    $hasFilters = request()->filled('search')
        || request()->filled('movement_type')
        || request()->filled('date_from')
        || request()->filled('date_to');
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Products</div>
          <h2 class="page-title">Stock history</h2>
          <div class="text-secondary small mt-1">Complete log of stock in, stock out, and adjustment entries across products.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#stock-movement-filters" role="button" aria-expanded="false"
              aria-controls="stock-movement-filters">Filters</a>
            <a href="{{ route('admin.products.inventory') }}" class="btn">Inventory monitoring</a>
            <a href="{{ route('admin.products') }}" class="btn">Catalog</a>
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
              <div class="text-secondary">Total records</div>
              <div class="h2 mb-0">{{ number_format($totalRecords) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Stock in</div>
              <div class="h2 mb-0 text-green">{{ number_format($countIn) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Stock out</div>
              <div class="h2 mb-0 text-red">{{ number_format($countOut) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Adjustments</div>
              <div class="h2 mb-0 text-yellow">{{ number_format($countAdj) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body border-bottom py-3">
          <form class="row g-3 align-items-end collapse" id="stock-movement-filters" method="GET"
            action="{{ route('admin.products.stock-movements') }}">
            <div class="col-lg-4">
              <label class="form-label" for="sm-search">Product</label>
              <input id="sm-search" type="text" class="form-control" name="search" placeholder="Search by product name…"
                value="{{ request('search') }}">
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="sm-type">Movement type</label>
              <select id="sm-type" class="form-select" name="movement_type">
                <option value="">All types</option>
                <option value="in" @selected(request('movement_type') === 'in')>Stock In</option>
                <option value="out" @selected(request('movement_type') === 'out')>Stock Out</option>
                <option value="adjustment" @selected(request('movement_type') === 'adjustment')>Adjustment</option>
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="sm-from">From</label>
              <input id="sm-from" type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="sm-to">To</label>
              <input id="sm-to" type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
            @if ($hasFilters)
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.products.stock-movements') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div id="stock-movements-table" class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead>
              <tr>
                <th><button type="button" class="table-sort" data-sort="sort-date">Date</button></th>
                <th><button type="button" class="table-sort" data-sort="sort-product">Product</button></th>
                <th><button type="button" class="table-sort" data-sort="sort-type">Movement type</button></th>
                <th class="text-end"><button type="button" class="table-sort" data-sort="sort-qty">Quantity</button></th>
                <th><button type="button" class="table-sort" data-sort="sort-ref">Reference</button></th>
                <th><button type="button" class="table-sort" data-sort="sort-notes">Notes</button></th>
              </tr>
            </thead>
            <tbody class="table-tbody">
              @forelse ($movements as $movement)
                @php
                  $qtySort = match ($movement->type) {
                    'in' => (int) $movement->quantity,
                    'out' => -1 * abs((int) $movement->quantity),
                    default => (int) $movement->quantity,
                  };
                  $isoDate = $movement->created_at?->format('Y-m-d H:i:s') ?? '';
                @endphp
                <tr>
                  <td class="sort-date text-secondary" data-date="{{ $isoDate }}">{{ $movement->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                  <td class="sort-product">
                    @if ($movement->product)
                      <a href="{{ route('admin.products.show', $movement->product_id) }}" class="fw-medium">{{ $movement->product->name }}</a>
                    @else
                      <span class="text-secondary">—</span>
                    @endif
                  </td>
                  <td class="sort-type" data-type="{{ $movement->type_label }}">
                    <span class="badge {{ $movement->type_badge }}">{{ $movement->type_label }}</span>
                  </td>
                  <td class="text-end font-monospace sort-qty" data-qty="{{ $qtySort }}">{{ $movement->signed_quantity }}</td>
                  <td class="sort-ref text-secondary">{{ $movement->reference ? $movement->reference : '—' }}</td>
                  <td class="sort-notes">{{ $movement->notes ? $movement->notes : '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-5">
                    @if ($hasFilters)
                      No movements match the current filters.
                    @else
                      No stock movements recorded yet.
                    @endif
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-secondary small">Movement types: Stock In, Stock Out, and Adjustment.</div>
          <div class="text-secondary small">Click column headers to sort this page.</div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('admin/assets/dist/libs/list.js/dist/list.min.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var el = document.getElementById('stock-movements-table');
      if (!el || !window.List) return;
      new List('stock-movements-table', {
        sortClass: 'table-sort',
        listClass: 'table-tbody',
        valueNames: [
          { attr: 'data-date', name: 'sort-date' },
          'sort-product',
          { attr: 'data-type', name: 'sort-type' },
          { attr: 'data-qty', name: 'sort-qty' },
          'sort-ref',
          'sort-notes',
        ]
      });
    });
  </script>
@endpush
