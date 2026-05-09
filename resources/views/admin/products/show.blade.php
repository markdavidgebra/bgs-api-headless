@extends('admin.layouts.master')

@php
  $movements = $product->stockMovements;
  $previewMovements = $movements->take(3);
  [$invLabel, $invBadge] = match ($product->stock_status) {
      'out_of_stock' => ['Out of stock', 'bg-red-lt'],
      'low_stock' => ['Low stock', 'bg-yellow-lt'],
      default => ['In stock', 'bg-green-lt'],
  };
  $statusBadge = match ($product->status) {
      'active' => 'bg-green-lt',
      'inactive' => 'bg-secondary-lt',
      'archived' => 'bg-red-lt',
      default => 'bg-azure-lt',
  };
  $statusLabel = ucfirst((string) $product->status);
  $sell = (float) $product->selling_price;
  $cost = (float) $product->cost_price;
  $margin = $sell > 0 ? (($sell - $cost) / $sell) * 100 : 0;
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-3 align-items-center">
        <div class="col-auto">
          @if ($product->image_url)
            <span class="avatar avatar-xl rounded" style="background-image: url({{ $product->image_url }})"></span>
          @else
            <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ $product->initial }}</span>
          @endif
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Inventory</div>
          <h2 class="page-title">{{ $product->name }}</h2>
          <div class="text-secondary mt-1 small">
            {{ $product->category ?? '—' }}
            @if ($product->sku)
              · <span class="font-monospace">{{ $product->sku }}</span>
            @endif
            · <span class="badge {{ $invBadge }}">{{ $invLabel }}</span>
            · <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
            @if (! $product->is_available_for_sale)
              · <span class="badge bg-orange-lt">Not for sale</span>
            @endif
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.products') }}" class="btn">Back to products</a>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">Edit product</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">On hand</div>
              <div class="d-flex align-items-baseline justify-content-between gap-2 flex-wrap">
                <div class="h2 mb-0">{{ number_format((int) $product->stock_quantity) }}</div>
                <span class="text-secondary small">{{ $product->unit ?? 'pcs' }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Minimum alert</div>
              <div class="h2 mb-0">{{ number_format((int) $product->minimum_stock_alert) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Selling price</div>
              <div class="h2 mb-0">₱{{ number_format($sell, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Margin (est.)</div>
              <div class="h2 mb-0">{{ number_format($margin, 0) }}%</div>
              <div class="text-secondary small mt-1">vs cost ₱{{ number_format($cost, 2) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Product information</h3>
            </div>
            <div class="card-body">
              <div
                class="mb-4 rounded border bg-secondary-lt d-flex align-items-center justify-content-center p-3 overflow-hidden">
                @if ($product->image_url)
                  <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                    class="img-fluid rounded shadow-sm" style="max-height: 280px; max-width: 100%; object-fit: contain;">
                @else
                  <div class="text-secondary small py-5">No product image on file.</div>
                @endif
              </div>
              <div class="datagrid">
                <div class="datagrid-item">
                  <div class="datagrid-title">Product ID</div>
                  <div class="datagrid-content font-monospace">#{{ $product->id }}</div>
                </div>
                @if ($product->slug)
                  <div class="datagrid-item">
                    <div class="datagrid-title">Slug</div>
                    <div class="datagrid-content font-monospace">{{ $product->slug }}</div>
                  </div>
                @endif
                <div class="datagrid-item">
                  <div class="datagrid-title">Brand</div>
                  <div class="datagrid-content">{{ $product->brand ?? '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Description</div>
                  <div class="datagrid-content text-break">{{ $product->description ? $product->description : '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Supplier</div>
                  <div class="datagrid-content">{{ $product->supplier ?? '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Batch</div>
                  <div class="datagrid-content font-monospace">{{ $product->batch_number ?? '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Expiry</div>
                  <div class="datagrid-content">{{ $product->expiry_date?->format('M j, Y') ?? '—' }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Stock summary</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Status</div>
                  <div class="datagrid-content"><span class="badge {{ $invBadge }}">{{ $invLabel }}</span></div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">On hand</div>
                  <div class="datagrid-content">{{ number_format((int) $product->stock_quantity) }}
                    {{ $product->unit ?? 'pcs' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Reorder at</div>
                  <div class="datagrid-content">{{ number_format((int) $product->minimum_stock_alert) }}
                    {{ $product->unit ?? 'pcs' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Available for sale</div>
                  <div class="datagrid-content">{{ $product->is_available_for_sale ? 'Yes' : 'No' }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Pricing</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Cost price</div>
                  <div class="datagrid-content">₱{{ number_format($cost, 2) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Selling price</div>
                  <div class="datagrid-content fw-medium">₱{{ number_format($sell, 2) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Discount price</div>
                  <div class="datagrid-content">
                    @if ($product->discount_price !== null && (float) $product->discount_price > 0)
                      ₱{{ number_format((float) $product->discount_price, 2) }}
                    @else
                      <span class="text-secondary">—</span>
                    @endif
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">List / promo</div>
                  <div class="datagrid-content fw-medium">₱{{ $product->final_price }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
          <div>
            <h3 class="card-title mb-0">Movement history</h3>
            <div class="text-secondary small">Latest stock movements and adjustments.</div>
          </div>
          @if ($movements->isNotEmpty())
            <div class="ms-auto">
              <a href="#product-movements-full" class="btn btn-sm btn-primary" data-bs-toggle="collapse" role="button"
                aria-expanded="false" aria-controls="product-movements-full">Full history</a>
            </div>
          @endif
        </div>
        <div class="card-body pt-3">
          <div class="text-secondary small mb-2">Preview</div>
          @forelse ($previewMovements as $m)
            <div class="d-flex flex-wrap gap-2 justify-content-between border-bottom border-secondary border-opacity-25 py-2">
              <div class="d-flex align-items-center flex-wrap gap-1">
                <span class="badge {{ $m->type_badge }}">{{ $m->type_label }}</span>
                <span class="text-secondary">·</span>
                {{ $m->created_at?->format('M j, Y g:i A') ?? '—' }}
              </div>
              <div>
                <span class="fw-medium font-monospace">{{ $m->signed_quantity }}</span>
                <span class="text-secondary small ms-2">{{ $m->reference ?? '—' }}</span>
              </div>
            </div>
          @empty
            <div class="text-secondary">No movements recorded yet.</div>
          @endforelse
        </div>
        @if ($movements->isNotEmpty())
          <div class="collapse" id="product-movements-full">
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm mb-0">
                <thead>
                  <tr>
                    <th>Date &amp; time</th>
                    <th>Type</th>
                    <th class="text-end">Qty</th>
                    <th>Reference</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($movements as $m)
                    <tr>
                      <td class="text-secondary">{{ $m->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                      <td><span class="badge {{ $m->type_badge }}">{{ $m->type_label }}</span></td>
                      <td class="text-end font-monospace">{{ $m->signed_quantity }}</td>
                      <td>{{ $m->reference ?? '—' }}</td>
                      <td class="text-secondary small">{{ $m->notes ? $m->notes : '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
