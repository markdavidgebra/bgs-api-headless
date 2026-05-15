@extends('admin.layouts.master')

@php
  /** @var \Illuminate\Support\Collection $services */
  /** @var \Illuminate\Support\Collection $treatmentPackages */
  /** @var \Illuminate\Support\Collection $products */
  $oldServiceIds = collect(old('service_ids', []))->map(fn ($v) => (string) $v)->all();
  $oldPackageIds = collect(old('treatment_package_ids', []))->map(fn ($v) => (string) $v)->all();
  $oldProductIds = collect(old('product_ids', []))->map(fn ($v) => (string) $v)->all();
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-3 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Affiliate Code</div>
          <h2 class="page-title">Create Affiliate Code</h2>
          <div class="text-secondary small mt-1">Enter a manual code, set the discount, and choose which catalog items it applies to.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.affiliate-codes') }}" class="btn">Cancel</a>
            <button type="submit" form="affiliate-code-create-form" class="btn btn-primary">Save code</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="affiliate-code-create-form" method="POST" action="{{ route('admin.affiliate-codes.store') }}">
        @csrf

        <div class="row g-3">
          <div class="col-lg-8">
            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Code details</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="code">Affiliate code</label>
                    <input id="code" name="code" type="text"
                      class="form-control font-monospace text-uppercase @error('code') is-invalid @enderror"
                      value="{{ old('code') }}" required maxlength="64" placeholder="e.g. PARTNER2026"
                      autocomplete="off" spellcheck="false">
                    <small class="form-hint">Letters, numbers, hyphens, and underscores only. Saved in uppercase.</small>
                    @error('code')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="label">Label</label>
                    <input id="label" name="label" type="text" class="form-control @error('label') is-invalid @enderror"
                      value="{{ old('label') }}" maxlength="255" placeholder="Optional display name">
                    @error('label')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                      <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                      <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    @error('status')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="notes">Internal notes</label>
                    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Optional notes for staff">{{ old('notes') }}</textarea>
                    @error('notes')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Discount</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="discount_method">Discount type</label>
                    <select id="discount_method" name="discount_method"
                      class="form-select @error('discount_method') is-invalid @enderror" required>
                      <option value="percentage" @selected(old('discount_method', 'percentage') === 'percentage')>Percentage (%)</option>
                      <option value="fixed" @selected(old('discount_method') === 'fixed')>Fixed amount (₱)</option>
                    </select>
                    @error('discount_method')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="discount_value">Discount value</label>
                    <input id="discount_value" name="discount_value" type="number" min="0.01" step="0.01"
                      class="form-control @error('discount_value') is-invalid @enderror"
                      value="{{ old('discount_value') }}" required
                      placeholder="e.g. 15">
                    <small id="discount_value_hint" class="form-hint">Enter the percentage off (1–100).</small>
                    @error('discount_value')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Services</h3>
              </div>
              <div class="card-body">
                <p class="text-secondary small mb-3">Check each service this affiliate code applies to.</p>
                @if ($services->isEmpty())
                  <p class="text-secondary small mb-0">No services in the catalog yet.</p>
                @else
                  <div class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2" role="group"
                    aria-labelledby="affiliate-services-label">
                    <span id="affiliate-services-label" class="visually-hidden">Services</span>
                    @foreach ($services as $svc)
                      <div class="col">
                        <label class="form-check mb-0">
                          <input type="checkbox" class="form-check-input" name="service_ids[]" value="{{ $svc->id }}"
                            id="aff-svc-{{ $svc->id }}" @checked(in_array((string) $svc->id, $oldServiceIds, true))>
                          <span class="form-check-label">{{ $svc->name }}</span>
                        </label>
                      </div>
                    @endforeach
                  </div>
                @endif
                @error('service_ids')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Treatment packages</h3>
              </div>
              <div class="card-body">
                <p class="text-secondary small mb-3">Check each package this affiliate code applies to.</p>
                @if ($treatmentPackages->isEmpty())
                  <p class="text-secondary small mb-0">No treatment packages in the catalog yet.</p>
                @else
                  <div class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2" role="group"
                    aria-labelledby="affiliate-packages-label">
                    <span id="affiliate-packages-label" class="visually-hidden">Treatment packages</span>
                    @foreach ($treatmentPackages as $pkg)
                      <div class="col">
                        <label class="form-check mb-0">
                          <input type="checkbox" class="form-check-input" name="treatment_package_ids[]"
                            value="{{ $pkg->id }}" id="aff-pkg-{{ $pkg->id }}"
                            @checked(in_array((string) $pkg->id, $oldPackageIds, true))>
                          <span class="form-check-label">{{ $pkg->name }}</span>
                        </label>
                      </div>
                    @endforeach
                  </div>
                @endif
                @error('treatment_package_ids')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Products</h3>
              </div>
              <div class="card-body">
                <p class="text-secondary small mb-3">Check each product this affiliate code applies to.</p>
                @if ($products->isEmpty())
                  <p class="text-secondary small mb-0">No products in the catalog yet.</p>
                @else
                  <div class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2" role="group"
                    aria-labelledby="affiliate-products-label">
                    <span id="affiliate-products-label" class="visually-hidden">Products</span>
                    @foreach ($products as $prod)
                      <div class="col">
                        <label class="form-check mb-0">
                          <input type="checkbox" class="form-check-input" name="product_ids[]" value="{{ $prod->id }}"
                            id="aff-prod-{{ $prod->id }}" @checked(in_array((string) $prod->id, $oldProductIds, true))>
                          <span class="form-check-label">
                            {{ $prod->name }}@if ($prod->sku) <span class="text-secondary">({{ $prod->sku }})</span> @endif
                          </span>
                        </label>
                      </div>
                    @endforeach
                  </div>
                @endif
                @error('product_ids')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title mb-2">How it works</h3>
                <p class="text-secondary small mb-0">
                  The code is entered manually with a percentage or fixed discount. Link it to any combination of
                  services, treatment packages, and products. Select at least one item before saving.
                </p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      const methodEl = document.getElementById('discount_method');
      const valueEl = document.getElementById('discount_value');
      const hintEl = document.getElementById('discount_value_hint');
      if (!methodEl || !valueEl || !hintEl) return;

      function syncDiscountUi() {
        const isPercent = methodEl.value === 'percentage';
        valueEl.max = isPercent ? '100' : '';
        valueEl.placeholder = isPercent ? 'e.g. 15' : 'e.g. 500';
        hintEl.textContent = isPercent
          ? 'Enter the percentage off (1–100).'
          : 'Enter the fixed amount off in pesos (₱).';
      }

      methodEl.addEventListener('change', syncDiscountUi);
      syncDiscountUi();
    })();
  </script>
@endpush
