@extends('admin.layouts.master')

@section('content')
 

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Catalog</div>
          <h2 class="page-title">Services</h2>
          <div class="text-secondary small mt-1">Browse, review, and manage treatment services.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#service-filters" role="button" aria-expanded="true"
              aria-controls="service-filters">
              Filters
            </a>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">New service</a>
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
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total services</div>
              <div class="h2 mb-0">{{ count($services) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active</div>
              <div class="h2 mb-0">{{ $services->where('status', 'active')->count() }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Inactive</div>
              <div class="h2 mb-0">{{ $services->where('status', 'inactive')->count() }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Avg. price</div>
              <div class="h2 mb-0">
                ₱{{ number_format($avgPrice, 2) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="service-filters" method="GET" action="">
            <div class="col-lg-5">
              <label class="form-label" for="search">Search</label>
              <div class="input-icon">
                <span class="input-icon-addon">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    <path d="M21 21l-6 -6" />
                  </svg>
                </span>
                <input id="search" type="text" class="form-control" name="search" placeholder="Service, category…"
                  value="{{ request('search') }}">
              </div>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
              </select>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="category">Category</label>
              <input id="category" type="text" class="form-control" name="category" value="{{ request('category') }}"
                placeholder="e.g. Aesthetic">
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>

            @if (request()->filled('search') || request()->filled('status') || request()->filled('category'))
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.services') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div id="service-table" class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th><button class="table-sort" data-sort="sort-service">Service</button></th>
                <th><button class="table-sort" data-sort="sort-category">Summary</button></th>
                <th><button class="table-sort" data-sort="sort-duration">Duration</button></th>
                <th class="text-end"><button class="table-sort" data-sort="sort-price">Price</button></th>
                <th><button class="table-sort" data-sort="sort-status">Status</button></th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody class="table-tbody">
              @forelse ($services as $service)
                <tr>
                  <td class="sort-service">
                    <div class="d-flex align-items-center">
                    <span class="avatar avatar-sm rounded me-2" style="background-image: url('{{ $service->image_url }}')"></span>
                      <div>
                        <div class="fw-medium">{{ $service->name }}</div>
                        <div class="text-secondary small text-truncate" style="max-width: 260px;">
                          {{ \Illuminate\Support\Str::limit(strip_tags($service->description ?? ''), 120) ?: '—' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="sort-category">{{ $service->summary_text }}
                  <td class="sort-duration">{{ $service->duration_label }}
                 <td class="text-end sort-price" data-price="{{ $service->promo_price !== null ? $service->promo_price : $service->price }}">
                    @if ($service->promo_price !== null)
                      <span class="text-secondary text-decoration-line-through small d-block">₱{{ number_format($service->price, 2) }}</span>
                      <span class="fw-medium">₱{{ number_format($service->promo_price, 2) }}</span>
                    @else
                      ₱{{ number_format($service->price, 2) }}
                    @endif
                  </td>
                  <td class="sort-status" data-status="{{ $service->getStatusLabelAttribute() }}">
                    <span class="badge {{ $service->getStatusBadgeAttribute() }}">{{ ucfirst($service->getStatusLabelAttribute()) }}</span>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.services.show', $service->id) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm">Edit</a>
                      <form method="POST" action="{{ route('admin.services.destroy', $service->id) }}" onsubmit="return confirm('Delete this service? This cannot be undone.');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-5">No services found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-secondary small">Tip: click table headers to sort.</div>
          @if ($services instanceof \Illuminate\Pagination\LengthAwarePaginator && $services->hasPages())
            <div>{{ $services->links() }}</div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('admin/assets/dist/libs/list.js/dist/list.min.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!document.getElementById('service-table')) return;
      new List('service-table', {
        sortClass: 'table-sort',
        listClass: 'table-tbody',
        valueNames: [
          'sort-service',
          'sort-category',
          'sort-duration',
          { attr: 'data-price', name: 'sort-price' },
          { attr: 'data-status', name: 'sort-status' },
        ]
      });
    });
  </script>
@endpush