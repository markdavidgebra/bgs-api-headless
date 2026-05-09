@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $promotions */
    $discountMethods = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed amount',
        'free_service' => 'Free service',
        'bundle' => 'Bundle',
    ];
    $hasFilters =
        request()->filled('search') ||
        request()->filled('status') ||
        request()->filled('discount_method') ||
        request()->filled('applies_to') ||
        request()->filled('date');
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Marketing</div>
          <h2 class="page-title">Promotions / Offers</h2>
          <div class="text-secondary small mt-1">Campaigns and discounts from the database.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#promo-filters" role="button"
              aria-expanded="{{ $hasFilters ? 'true' : 'false' }}" aria-controls="promo-filters">Filters</a>
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
              </svg>
              Add promotion
            </a>
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
              <div class="text-secondary">Total promos</div>
              <div class="h2 mb-0">{{ number_format($totalPromos) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active</div>
              <div class="h2 mb-0 text-green">{{ number_format($activePromos) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Expired</div>
              <div class="h2 mb-0 text-yellow">{{ number_format($expiredPromos) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Drafts</div>
              <div class="h3 mb-0">{{ number_format($draftPromos) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Service scope</div>
              <div class="h3 mb-0">{{ number_format($servicePromos) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Package scope</div>
              <div class="h3 mb-0">{{ number_format($packagePromos) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Membership scope</div>
              <div class="h3 mb-0">{{ number_format($membershipPromos) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Product scope</div>
              <div class="h3 mb-0">{{ number_format($productPromos) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body border-bottom py-3">
          <form class="row g-3 align-items-end collapse {{ $hasFilters ? 'show' : '' }}" id="promo-filters" method="GET"
            action="{{ route('admin.promotions') }}">
            <div class="col-lg-3">
              <label class="form-label" for="search">Search</label>
              <input id="search" type="text" class="form-control" name="search" value="{{ request('search') }}"
                placeholder="Name or code…">
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                @foreach (['draft', 'active', 'scheduled', 'expired', 'inactive'] as $st)
                  <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="discount_method">Discount type</label>
              <select id="discount_method" class="form-select" name="discount_method">
                <option value="">All</option>
                @foreach ($discountMethods as $value => $label)
                  <option value="{{ $value }}" @selected(request('discount_method') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="applies_to">Applies to</label>
              <select id="applies_to" class="form-select" name="applies_to">
                <option value="">All</option>
                <option value="service" @selected(request('applies_to') === 'service')>Services</option>
                <option value="package" @selected(request('applies_to') === 'package')>Packages</option>
                <option value="membership" @selected(request('applies_to') === 'membership')>Memberships</option>
                <option value="product" @selected(request('applies_to') === 'product')>Products</option>
                <option value="all" @selected(request('applies_to') === 'all')>All</option>
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="date">Valid on date</label>
              <input id="date" type="date" class="form-control" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-lg-1 d-grid">
              <label class="form-label d-none d-lg-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>

            @if ($hasFilters)
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.promotions') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary small fw-bold">Promotion</th>
                <th class="text-uppercase text-secondary small fw-bold">Type</th>
                <th class="text-uppercase text-secondary small fw-bold">Applies to</th>
                <th class="text-uppercase text-secondary small fw-bold">Discount</th>
                <th class="text-uppercase text-secondary small fw-bold">Validity</th>
                <th class="text-uppercase text-secondary small fw-bold">Status</th>
                <th class="text-end text-uppercase text-secondary small fw-bold">Usage cap</th>
                <th class="w-1 text-uppercase text-secondary small fw-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($promotions as $promotion)
                @php
                  $typeLabel = $discountMethods[$promotion->discount_method ?? ''] ??
                      ($promotion->discount_method ? ucfirst(str_replace('_', ' ', $promotion->discount_method)) : '—');
                @endphp
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <span
                        class="avatar avatar-sm rounded bg-pink-lt text-pink me-2">{{ $promotion->initial }}</span>
                      <div>
                        <div class="fw-medium">{{ $promotion->name }}</div>
                        @if ($promotion->code)
                          <div class="text-secondary small font-monospace">{{ $promotion->code }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    <div>{{ $typeLabel }}</div>
                    @if ($promotion->type)
                      <div class="text-secondary small">{{ ucfirst($promotion->type) }}</div>
                    @endif
                  </td>
                  <td>{{ $promotion->scope_label }}</td>
                  <td class="font-monospace">{{ $promotion->discount_label }}</td>
                  <td class="text-secondary small">{{ $promotion->validity_label ?? '—' }}</td>
                  <td>
                    <span class="badge {{ $promotion->status_badge }}">{{ ucfirst((string) $promotion->status) }}</span>
                  </td>
                  <td class="text-end text-secondary small">
                    @if ($promotion->usage_limit !== null)
                      {{ number_format((int) $promotion->usage_limit) }}
                    @else
                      Unlimited
                    @endif
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.promotions.show', ['id' => $promotion->id]) }}"
                        class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.promotions.edit', ['id' => $promotion->id]) }}"
                        class="btn btn-sm">Edit</a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-secondary py-4">No promotions found. Add one or clear filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-secondary small">Statuses and scopes match your promotions migration.</div>
          <div>{{ $promotions->links() }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
