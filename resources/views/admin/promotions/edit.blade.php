@extends('admin.layouts.master')

@php
  /** @var \App\Models\Promotion $promotion */
  /** @var \Illuminate\Support\Collection $services */
  /** @var \Illuminate\Support\Collection $treatmentPackages */
  /** @var \Illuminate\Support\Collection $membershipPlans */
  /** @var \Illuminate\Support\Collection $products */
  $planInitial = strtoupper(\Illuminate\Support\Str::substr((string) old('name', $promotion->name), 0, 1));
  $dayOptions = [
    ['mon', 'Mon'],
    ['tue', 'Tue'],
    ['wed', 'Wed'],
    ['thu', 'Thu'],
    ['fri', 'Fri'],
    ['sat', 'Sat'],
    ['sun', 'Sun'],
  ];
  $timeLimitDefault = '';
  if ($promotion->time_limit) {
    $tl = $promotion->time_limit;
    $timeLimitDefault = $tl instanceof \DateTimeInterface
      ? $tl->format('H:i')
      : \Illuminate\Support\Str::substr((string) $tl, 0, 5);
  }
  $serviceIdsDefault = $promotion->services->pluck('id')->all();
  $packageIdsDefault = $promotion->treatmentPackages->pluck('id')->all();
  $planIdsDefault = $promotion->membershipPlans->pluck('id')->all();
  $productIdsDefault = $promotion->products->pluck('id')->all();
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-pink-lt text-pink">{{ $planInitial }}</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Promotions</div>
          <h2 class="page-title mb-0">{{ old('name', $promotion->name) }}</h2>
          <div class="text-secondary small mt-1">
            ID <span class="font-monospace">#{{ $promotion->id }}</span> · update all fields on this page.
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn">Cancel</a>
            <button type="submit" form="promotion-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form id="promotion-edit-form" method="POST" action="{{ route('admin.promotions.update', $promotion->id) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Basic information</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="name">Promotion name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name', $promotion->name) }}" required maxlength="255">
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="code">Code</label>
                    <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror"
                      value="{{ old('code', $promotion->code) }}" maxlength="255" placeholder="Optional unique code">
                    @error('code')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="type">Campaign type</label>
                    <input id="type" name="type" type="text" class="form-control @error('type') is-invalid @enderror"
                      value="{{ old('type', $promotion->type) }}" maxlength="255">
                    @error('type')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                      @foreach (['draft', 'active', 'scheduled', 'expired', 'inactive'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $promotion->status) === $st)>{{ ucfirst($st) }}
                        </option>
                      @endforeach
                    </select>
                    @error('status')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Staff-facing summary">{{ old('description', $promotion->description) }}</textarea>
                    @error('description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="image">Banner / image</label>
                    @if ($promotion->image_url)
                      <div class="mb-2">
                        <img src="{{ $promotion->image_url }}" alt="" class="rounded border" style="max-height: 120px">
                      </div>
                    @endif
                    <input id="image" name="image" type="file" accept="image/*"
                      class="form-control @error('image') is-invalid @enderror">
                    <small class="form-hint">Leave empty to keep the current image.</small>
                    @error('image')
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
                    <label class="form-label" for="discount_method">Discount method</label>
                    <select id="discount_method" name="discount_method"
                      class="form-select @error('discount_method') is-invalid @enderror">
                      <option value="">— Select —</option>
                      <option value="percentage" @selected(old('discount_method', $promotion->discount_method) === 'percentage')>Percentage</option>
                      <option value="fixed" @selected(old('discount_method', $promotion->discount_method) === 'fixed')>Fixed amount (₱)</option>
                      <option value="free_service" @selected(old('discount_method', $promotion->discount_method) === 'free_service')>Free service</option>
                      <option value="bundle" @selected(old('discount_method', $promotion->discount_method) === 'bundle')>Bundle</option>
                    </select>
                    @error('discount_method')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="discount_value">Discount value</label>
                    <input id="discount_value" name="discount_value" type="number" min="0" step="0.01"
                      class="form-control @error('discount_value') is-invalid @enderror"
                      value="{{ old('discount_value', $promotion->discount_value) }}">
                    @error('discount_value')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="minimum_spend">Minimum spend (₱)</label>
                    <input id="minimum_spend" name="minimum_spend" type="number" min="0" step="0.01"
                      class="form-control @error('minimum_spend') is-invalid @enderror"
                      value="{{ old('minimum_spend', $promotion->minimum_spend) }}">
                    @error('minimum_spend')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="maximum_discount">Maximum discount (₱)</label>
                    <input id="maximum_discount" name="maximum_discount" type="number" min="0" step="0.01"
                      class="form-control @error('maximum_discount') is-invalid @enderror"
                      value="{{ old('maximum_discount', $promotion->maximum_discount) }}">
                    @error('maximum_discount')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Applies to</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label required" for="applies_to">Scope</label>
                    <select id="applies_to" name="applies_to" class="form-select @error('applies_to') is-invalid @enderror"
                      required>
                      <option value="services" @selected(old('applies_to', $promotion->applies_to) === 'services')>Services
                      </option>
                      <option value="packages" @selected(old('applies_to', $promotion->applies_to) === 'packages')>Treatment packages
                      </option>
                      <option value="memberships" @selected(old('applies_to', $promotion->applies_to) === 'memberships')>Membership plans
                      </option>
                      <option value="products" @selected(old('applies_to', $promotion->applies_to) === 'products')>Products
                      </option>
                      <option value="all" @selected(old('applies_to', $promotion->applies_to) === 'all')>All</option>
                    </select>
                    @error('applies_to')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12 js-applies-field d-none" data-show-for="services">
                    <label class="form-label" for="service_ids">Services</label>
                    <select id="service_ids" name="service_ids[]" class="form-select" multiple size="8">
                      @foreach ($services as $svc)
                        <option value="{{ $svc->id }}"
                          @selected(collect(old('service_ids', $serviceIdsDefault))->map(fn ($v) => (int) $v)->contains($svc->id))>{{ $svc->name }}
                        </option>
                      @endforeach
                    </select>
                    <small class="form-hint">Ctrl/Cmd + click for multiple.</small>
                  </div>

                  <div class="col-12 js-applies-field d-none" data-show-for="packages">
                    <label class="form-label" for="treatment_package_ids">Treatment packages</label>
                    <select id="treatment_package_ids" name="treatment_package_ids[]" class="form-select" multiple
                      size="8">
                      @foreach ($treatmentPackages as $pkg)
                        <option value="{{ $pkg->id }}"
                          @selected(collect(old('treatment_package_ids', $packageIdsDefault))->map(fn ($v) => (int) $v)->contains($pkg->id))>{{ $pkg->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-12 js-applies-field d-none" data-show-for="memberships">
                    <label class="form-label" for="membership_plan_ids">Membership plans</label>
                    <select id="membership_plan_ids" name="membership_plan_ids[]" class="form-select" multiple size="8">
                      @foreach ($membershipPlans as $plan)
                        <option value="{{ $plan->id }}"
                          @selected(collect(old('membership_plan_ids', $planIdsDefault))->map(fn ($v) => (int) $v)->contains($plan->id))>{{ $plan->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-12 js-applies-field d-none" data-show-for="products">
                    <label class="form-label" for="product_ids">Products</label>
                    <select id="product_ids" name="product_ids[]" class="form-select" multiple size="8">
                      @foreach ($products as $prod)
                        <option value="{{ $prod->id }}"
                          @selected(collect(old('product_ids', $productIdsDefault))->map(fn ($v) => (int) $v)->contains($prod->id))>{{ $prod->name }}@if ($prod->sku)
                            ({{ $prod->sku }})
                          @endif
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Validity & scheduling</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label" for="start_date">Start date</label>
                    <input id="start_date" name="start_date" type="date"
                      class="form-control @error('start_date') is-invalid @enderror"
                      value="{{ old('start_date', $promotion->start_date?->format('Y-m-d')) }}">
                    @error('start_date')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="end_date">End date</label>
                    <input id="end_date" name="end_date" type="date"
                      class="form-control @error('end_date') is-invalid @enderror"
                      value="{{ old('end_date', $promotion->end_date?->format('Y-m-d')) }}">
                    @error('end_date')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="time_limit">Daily cutoff (time)</label>
                    <input id="time_limit" name="time_limit" type="time"
                      class="form-control @error('time_limit') is-invalid @enderror"
                      value="{{ old('time_limit', $timeLimitDefault) }}">
                    @error('time_limit')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label d-block">Available days</label>
                    <div class="row g-2">
                      @foreach ($dayOptions as [$val, $label])
                        <div class="col-auto">
                          <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="available_days[]" value="{{ $val }}"
                              @checked(in_array($val, old('available_days', $promotion->available_days ?? []), true))>
                            <span class="form-check-label">{{ $label }}</span>
                          </label>
                        </div>
                      @endforeach
                    </div>
                    @error('available_days')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Usage rules</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="usage_limit">Total usage limit</label>
                    <input id="usage_limit" name="usage_limit" type="number" min="0" step="1"
                      class="form-control @error('usage_limit') is-invalid @enderror"
                      value="{{ old('usage_limit', $promotion->usage_limit) }}">
                    @error('usage_limit')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="limit_per_patient">Limit per patient</label>
                    <input id="limit_per_patient" name="limit_per_patient" type="number" min="0" step="1"
                      class="form-control @error('limit_per_patient') is-invalid @enderror"
                      value="{{ old('limit_per_patient', $promotion->limit_per_patient) }}">
                    @error('limit_per_patient')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <input type="hidden" name="new_patients_only" value="0">
                    <label class="form-check">
                      <input class="form-check-input" type="checkbox" name="new_patients_only" value="1"
                        @checked(old('new_patients_only', $promotion->new_patients_only))>
                      <span class="form-check-label">New patients only</span>
                    </label>
                  </div>
                  <div class="col-12">
                    <input type="hidden" name="can_combine_with_other_promos" value="0">
                    <label class="form-check">
                      <input class="form-check-input" type="checkbox" name="can_combine_with_other_promos" value="1"
                        @checked(old('can_combine_with_other_promos', $promotion->can_combine_with_other_promos))>
                      <span class="form-check-label">Can combine with other promos</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Terms & notes</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="terms_and_conditions">Terms and conditions</label>
                    <textarea id="terms_and_conditions" name="terms_and_conditions" rows="4"
                      class="form-control @error('terms_and_conditions') is-invalid @enderror">{{ old('terms_and_conditions', $promotion->terms_and_conditions) }}</textarea>
                    @error('terms_and_conditions')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="internal_notes">Internal notes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="3"
                      class="form-control @error('internal_notes') is-invalid @enderror">{{ old('internal_notes', $promotion->internal_notes) }}</textarea>
                    @error('internal_notes')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="display_note">Customer-facing display note</label>
                    <textarea id="display_note" name="display_note" rows="3"
                      class="form-control @error('display_note') is-invalid @enderror">{{ old('display_note', $promotion->display_note) }}</textarea>
                    @error('display_note')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-4">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Snapshot</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Code</div>
                  <div class="datagrid-content font-monospace">{{ $promotion->code ?? '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Status</div>
                  <div class="datagrid-content">
                    <span class="badge {{ $promotion->status_badge }}">{{ ucfirst((string) $promotion->status) }}</span>
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Discount</div>
                  <div class="datagrid-content">{{ $promotion->discount_label }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Validity</div>
                  <div class="datagrid-content">{{ $promotion->validity_label ?? '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Scope</div>
                  <div class="datagrid-content">{{ $promotion->scope_label }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Tips</h3>
            </div>
            <div class="card-body text-secondary small">
              Changing scope will re-sync pivot links: unrelated lists are cleared on save.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      var scope = document.getElementById('applies_to');
      var fields = document.querySelectorAll('.js-applies-field');
      if (!scope || !fields.length) return;

      function refresh() {
        var v = scope.value;
        fields.forEach(function (el) {
          var forVal = el.getAttribute('data-show-for');
          var show = v === 'all' || (v && forVal === v);
          el.classList.toggle('d-none', !show);
        });
      }

      scope.addEventListener('change', refresh);
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refresh);
      } else {
        refresh();
      }
    })();
  </script>
@endpush
