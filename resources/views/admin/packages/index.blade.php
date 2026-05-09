@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Catalog</div>
          <h2 class="page-title">Treatment packages</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#package-filters" role="button" aria-expanded="true"
              aria-controls="package-filters">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5.5 5h13" />
                <path d="M5.5 12h13" />
                <path d="M5.5 19h13" />
                <path d="M4 5l0 .01" />
                <path d="M4 12l0 .01" />
                <path d="M4 19l0 .01" />
              </svg>
              Filters
            </a>
            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
              </svg>
              New package
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="package-filters" method="GET"
            action="{{ route('admin.packages') }}">
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
                <input id="search" type="text" class="form-control" name="search" placeholder="Search packages..."
                  value="{{ request('search') }}">
              </div>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
              </select>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="category">Category</label>
              <select id="category" class="form-select" name="category">
                <option value="">All</option>
                <option value="wellness" {{ request('category') == 'wellness' ? 'selected' : '' }}>Wellness</option>
                <option value="rehab" {{ request('category') == 'rehab' ? 'selected' : '' }}>Rehabilitation</option>
                <option value="chronic" {{ request('category') == 'chronic' ? 'selected' : '' }}>Chronic care</option>
              </select>
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>

            @if (request()->filled('search') || request()->filled('status') || request()->filled('category') || request()->filled('is_active'))
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.packages') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div id="table-default" class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th><button class="table-sort" data-sort="sort-name">Package</button></th>
                <th class="text-secondary">Included services</th>
                <th class="text-end"><button class="table-sort" data-sort="sort-sessions">Sessions</button></th>
                <th class="text-end"><button class="table-sort" data-sort="sort-price">Price</button></th>
                <th><button class="table-sort" data-sort="sort-validity">Validity</button></th>
                <th><button class="table-sort" data-sort="sort-status">Status</button></th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody class="table-tbody">
              @forelse ($packages as $pkg)
                @php
                  $sessionTotal = (int) $pkg->services->sum(fn ($s) => (int) $s->pivot->sessions);
                  $serviceNames = $pkg->services->pluck('name')->filter()->implode(', ');
                  $validitySort = 0;
                  if ($pkg->validity_value && $pkg->validity_type) {
                      $validitySort = match ($pkg->validity_type) {
                          'days' => (int) $pkg->validity_value,
                          'months' => (int) $pkg->validity_value * 30,
                          'years' => (int) $pkg->validity_value * 365,
                          default => 0,
                      };
                  }
                  $validityLabel = '—';
                  if ($pkg->validity_value && $pkg->validity_type) {
                      $unit = match ($pkg->validity_type) {
                          'days' => 'days',
                          'months' => 'months',
                          'years' => 'years',
                          default => $pkg->validity_type,
                      };
                      $validityLabel = $pkg->validity_value . ' ' . $unit;
                  }
                  $status = $pkg->status ?? 'active';
                  $statusBadge =
                      match ($status) {
                          'active' => 'bg-green-lt',
                          'pending' => 'bg-yellow-lt',
                          'archived' => 'bg-secondary-lt',
                          default => 'bg-secondary-lt',
                      };
                @endphp
                <tr>
                  <td class="sort-name">
                    <div class="d-flex align-items-center">
                      <span
                        class="avatar avatar-sm rounded bg-azure-lt text-azure me-2">{{ $pkg->initial }}</span>
                      <div>
                        <div class="fw-medium">{{ $pkg->name }}</div>
                        <div class="text-secondary small text-capitalize">{{ $pkg->category ?: '—' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-secondary">{{ $serviceNames !== '' ? $serviceNames : '—' }}</td>
                  <td class="text-end sort-sessions" data-sessions="{{ $sessionTotal }}">{{ $sessionTotal }}</td>
                  <td class="text-end sort-price" data-price="{{ $pkg->price }}">₱{{ number_format((float) $pkg->price, 2) }}
                  </td>
                  <td class="sort-validity" data-validity="{{ $validitySort }}">{{ $validityLabel }}</td>
                  <td class="sort-status" data-status="{{ $status }}"><span
                      class="badge {{ $statusBadge }}">{{ ucfirst($status) }}</span></td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.packages.show', $pkg) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.packages.edit', $pkg) }}" class="btn btn-sm">Edit</a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-secondary">No packages yet. <a href="{{ route('admin.packages.create') }}"
                      class="link-primary">Create one</a>.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-secondary small">
              Tip: click table headers to sort.
            </div>
            <div>
              {{ $packages->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script src="{{ asset('admin/assets/dist/libs/list.js/dist/list.min.js') }}" defer></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const list = new List('table-default', {
          sortClass: 'table-sort',
          listClass: 'table-tbody',
          valueNames: [
            'sort-name',
            { attr: 'data-sessions', name: 'sort-sessions' },
            { attr: 'data-price', name: 'sort-price' },
            { attr: 'data-validity', name: 'sort-validity' },
            { attr: 'data-status', name: 'sort-status' },
          ]
        });
      });
    </script>
  @endpush
@endsection