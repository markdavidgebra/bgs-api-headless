@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
  @endphp
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Products</h2>
          <div class="text-secondary small mt-1">Manage products, pricing, and inventory status.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#product-filters" role="button" aria-expanded="true"
              aria-controls="product-filters">Filters</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add product</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="product-filters" method="GET"
            action="{{ route('admin.products') }}">
            <div class="col-lg-5">
              <label class="form-label" for="search">Search</label>
              <input id="search" type="text" class="form-control" name="search" placeholder="Name, SKU, or brand…"
                value="{{ request('search') }}">
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="category">Category</label>
              <input id="category" type="text" class="form-control" name="category" placeholder="e.g. Skincare"
                value="{{ request('category') }}">
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="status">Stock status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="in_stock" @selected(request('status') === 'in_stock')>In stock</option>
                <option value="low_stock" @selected(request('status') === 'low_stock')>Low stock</option>
                <option value="out_of_stock" @selected(request('status') === 'out_of_stock')>Out of stock</option>
              </select>
            </div>
            <div class="col-lg-1 d-grid">
              <label class="form-label d-none d-lg-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>

            @if (request()->filled('search') || request()->filled('category') || request()->filled('status'))
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.products') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary small fw-bold">Product name</th>
                <th class="text-uppercase text-secondary small fw-bold">Category</th>
                <th class="text-end text-uppercase text-secondary small fw-bold">Price</th>
                <th class="text-end text-uppercase text-secondary small fw-bold">Stock</th>
                <th class="text-uppercase text-secondary small fw-bold">Status</th>
                <th class="w-1 text-uppercase text-secondary small fw-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($products as $product)
                @php
                  $rowStatus = $product->stock_status;
                  [$statusLabel, $badgeClass] = match ($rowStatus) {
                      'out_of_stock' => ['Out of stock', 'bg-red-lt'],
                      'low_stock' => ['Low stock', 'bg-yellow-lt'],
                      default => ['In stock', 'bg-green-lt'],
                  };
                  $listPrice = (float) ($product->discount_price ?? $product->selling_price ?? 0);
                @endphp
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      @if ($product->image_url)
                        <span class="avatar avatar-sm rounded me-2"
                          style="background-image: url({{ $product->image_url }})"></span>
                      @else
                        <span
                          class="avatar avatar-sm rounded bg-azure-lt text-azure me-2">{{ $product->initial }}</span>
                      @endif
                      <div>
                        <div class="fw-medium">{{ $product->name }}</div>
                        @if ($product->sku)
                          <div class="text-secondary small font-monospace">{{ $product->sku }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>{{ $product->category ?? '—' }}</td>
                  <td class="text-end">₱{{ number_format($listPrice, 2) }}</td>
                  <td class="text-end">{{ number_format((int) $product->stock_quantity) }}</td>
                  <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm">Edit</a>
                      <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This cannot be undone.');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-4">No products found. Add a product or adjust
                    filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-secondary small">
            Showing inventory status (in stock / low / out) from current quantities.
          </div>
          <div>{{ $products->links() }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
