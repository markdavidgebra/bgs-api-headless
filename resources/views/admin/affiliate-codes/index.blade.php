@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-3 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Affiliate Code</div>
          <h2 class="page-title">All Codes</h2>
          <div class="text-secondary small mt-1">Manual affiliate codes and their linked catalog items.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.affiliate-codes.create') }}" class="btn btn-primary">Create affiliate code</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary small fw-bold">Code</th>
                <th class="text-uppercase text-secondary small fw-bold">Label</th>
                <th class="text-uppercase text-secondary small fw-bold">Discount</th>
                <th class="text-uppercase text-secondary small fw-bold">Effectivity</th>
                <th class="text-uppercase text-secondary small fw-bold">Applies to</th>
                <th class="text-uppercase text-secondary small fw-bold text-end">Times used</th>
                <th class="text-uppercase text-secondary small fw-bold">Status</th>
                <th class="text-uppercase text-secondary small fw-bold">Created</th>
                <th class="text-uppercase text-secondary small fw-bold w-1">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($affiliateCodes as $affiliateCode)
                @php
                  $scopeParts = [];
                  if ($affiliateCode->services_count > 0) {
                      $scopeParts[] = $affiliateCode->services_count.' service'.($affiliateCode->services_count === 1 ? '' : 's');
                  }
                  if ($affiliateCode->treatment_packages_count > 0) {
                      $scopeParts[] = $affiliateCode->treatment_packages_count.' package'.($affiliateCode->treatment_packages_count === 1 ? '' : 's');
                  }
                  if ($affiliateCode->membership_plans_count > 0) {
                      $scopeParts[] = $affiliateCode->membership_plans_count.' plan'.($affiliateCode->membership_plans_count === 1 ? '' : 's');
                  }
                  if ($affiliateCode->products_count > 0) {
                      $scopeParts[] = $affiliateCode->products_count.' product'.($affiliateCode->products_count === 1 ? '' : 's');
                  }
                @endphp
                <tr>
                  <td>
                    <span class="font-monospace fw-semibold">{{ $affiliateCode->code }}</span>
                  </td>
                  <td class="text-secondary">{{ $affiliateCode->label ?: '—' }}</td>
                  <td class="font-monospace">{{ $affiliateCode->discount_label }}</td>
                  <td class="text-secondary small">{{ $affiliateCode->effectivity_label }}</td>
                  <td class="text-secondary small">
                    {{ $scopeParts !== [] ? implode(' · ', $scopeParts) : '—' }}
                  </td>
                  <td class="text-end font-monospace">
                    {{ number_format((int) $affiliateCode->times_used) }}
                  </td>
                  <td>
                    <span class="badge {{ $affiliateCode->status_badge }}">{{ ucfirst($affiliateCode->status) }}</span>
                  </td>
                  <td class="text-secondary">{{ $affiliateCode->created_at?->format('M j, Y') }}</td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.affiliate-codes.edit', $affiliateCode) }}" class="btn btn-sm">Edit</a>
                      <form method="POST" action="{{ route('admin.affiliate-codes.destroy', $affiliateCode) }}"
                        onsubmit="return confirm('Delete affiliate code {{ $affiliateCode->code }}? This cannot be undone.');"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-secondary py-5">
                    No affiliate codes yet.
                    <a href="{{ route('admin.affiliate-codes.create') }}" class="ms-1">Create one</a>.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($affiliateCodes->hasPages())
          <div class="card-footer d-flex align-items-center">
            {{ $affiliateCodes->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
