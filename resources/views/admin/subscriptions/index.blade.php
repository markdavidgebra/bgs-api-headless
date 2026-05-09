@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Plans</div>
          <h2 class="page-title">Subscription / Membership Plans</h2>
          <div class="text-secondary small mt-1">Manage plan offers, pricing, and subscriber performance.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#plan-filters" role="button" aria-expanded="true"
              aria-controls="plan-filters">
              Filters
            </a>
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">New plan</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total plans</div>
              <div class="h2 mb-0">{{ $stats['total_plans'] }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active plans</div>
              <div class="h2 mb-0">{{ $stats['active_plans'] }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active subscribers</div>
              <div class="h2 mb-0">{{ number_format($stats['active_subscribers']) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="plan-filters" method="GET"
            action="{{ route('admin.subscriptions') }}">
            <div class="col-lg-4">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
              </select>
            </div>
            <div class="col-lg-4">
              <label class="form-label" for="billing">Billing</label>
              <select id="billing" class="form-select" name="billing">
                <option value="">All</option>
                <option value="monthly" @selected(request('billing') === 'monthly')>Monthly</option>
                <option value="yearly" @selected(request('billing') === 'yearly')>Yearly</option>
              </select>
            </div>
            <div class="col-lg-3 d-grid">
              <label class="form-label d-none d-lg-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>

            @if (request()->filled('status') || request()->filled('billing'))
              <div class="col-12">
                <div class="text-secondary small">
                  Filters are active.
                  <a class="link-primary" href="{{ route('admin.subscriptions') }}">Clear</a>
                </div>
              </div>
            @endif
          </form>
        </div>

        <div id="subscription-table" class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>
                  <button type="button" class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-name">Plan name</button>
                </th>
                <th>
                  <button type="button" class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-type">Type</button>
                </th>
                <th class="text-end">
                  <button type="button" class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-price">Price</button>
                </th>
                <th class="text-uppercase text-secondary small fw-bold">Included benefits</th>
                <th class="text-end">
                  <button type="button" class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-subscribers">Active subscribers</button>
                </th>
                <th>
                  <button type="button" class="table-sort border-0 bg-transparent p-0 text-uppercase text-secondary small fw-bold"
                    data-sort="sort-status">Status</button>
                </th>
                <th class="w-1 text-uppercase text-secondary small fw-bold">Actions</th>
              </tr>
            </thead>
            <tbody class="table-tbody">
              @forelse ($plans as $plan)
                @php
                  $statusBadge =
                      $plan->status === 'active'
                          ? 'bg-green-lt'
                          : ($plan->status === 'paused'
                              ? 'bg-yellow-lt'
                              : 'bg-secondary-lt');
                  $typeLabel = $plan->billing_cycle
                      ? ucfirst($plan->billing_cycle)
                      : ucfirst((string) ($plan->type ?? '—'));
                  $benefitParts = $plan->services->map(
                      fn ($s) => $s->name . ' × ' . (int) ($s->pivot->sessions ?? 1),
                  );
                  $benefitsLine = $benefitParts->take(2)->implode(' • ');
                  $benefitsExtra = $benefitParts->count() > 2 ? $benefitParts->count() - 2 : 0;
                @endphp
                <tr>
                  <td class="sort-name">
                    <div class="d-flex align-items-center">
                      <span class="avatar avatar-sm rounded bg-azure-lt text-azure me-2">{{ strtoupper(\Illuminate\Support\Str::substr($plan->name, 0, 1)) }}</span>
                      <div>
                        <div class="fw-medium">{{ $plan->name }}</div>
                        @if ($plan->slug)
                          <div class="text-secondary small font-monospace">{{ $plan->slug }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td class="sort-type" data-type="{{ $typeLabel }}">{{ $typeLabel }}</td>
                  <td class="text-end sort-price" data-price="{{ $plan->price }}">₱{{ number_format((float) $plan->price, 2) }}</td>
                  <td>
                    @if ($benefitParts->isEmpty())
                      <span class="text-secondary small">—</span>
                    @else
                      <div class="text-secondary small">
                        {{ $benefitsLine }}
                        @if ($benefitsExtra > 0)
                          • +{{ $benefitsExtra }} more
                        @endif
                      </div>
                    @endif
                  </td>
                  <td class="text-end sort-subscribers" data-subscribers="{{ (int) $plan->active_subscribers_count }}">
                    {{ number_format((int) $plan->active_subscribers_count) }}
                  </td>
                  <td class="sort-status" data-status="{{ $plan->status }}">
                    <span class="badge {{ $statusBadge }}">{{ ucfirst((string) $plan->status) }}</span>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.subscriptions.show', $plan) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="btn btn-sm">Edit</a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-secondary py-4">No plans found. Create a plan or adjust filters.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-secondary small">Tip: click sortable headers to sort the current page.</div>
            <div>{{ $plans->links() }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('admin/assets/dist/libs/list.js/dist/list.min.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!document.getElementById('subscription-table')) return;
      new List('subscription-table', {
        sortClass: 'table-sort',
        listClass: 'table-tbody',
        valueNames: [
          'sort-name',
          { attr: 'data-type', name: 'sort-type' },
          { attr: 'data-price', name: 'sort-price' },
          { attr: 'data-subscribers', name: 'sort-subscribers' },
          { attr: 'data-status', name: 'sort-status' },
        ]
      });
    });
  </script>
@endpush
