@extends('admin.layouts.master')

@php
  $services = $services ?? collect();
  $title = old('name', $plan->name);
  $planInitial = strtoupper(\Illuminate\Support\Str::substr($title, 0, 1));
  $selectedIds = collect(old('included_service_ids', $plan->services->pluck('id')->all()))
      ->map(fn ($v) => (string) $v)
      ->all();
  $serviceSessionsFromPlan = $plan->services
      ->mapWithKeys(fn ($s) => [(string) $s->id => (string) $s->pivot->sessions])
      ->all();
  $rollDefault = $plan->rollover_unused_sessions ? '1' : '0';
  $cancelDefault = $plan->cancellation_allowed ? '1' : '0';
  $pauseDefault = $plan->pause_allowed ? '1' : '0';
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ $planInitial }}</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Subscriptions</div>
          <h2 class="page-title mb-0">{{ $title }}</h2>
          <div class="text-secondary small mt-1">
            Edit plan · ID <span class="font-monospace">#{{ $plan->id }}</span>
            @if ($plan->slug)
              · <span class="font-monospace">{{ $plan->slug }}</span>
            @endif
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.subscriptions.show', $plan) }}" class="btn">Cancel</a>
            <button type="submit" form="subscription-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form id="subscription-edit-form" method="POST" action="{{ route('admin.subscriptions.update', $plan) }}">
            @csrf
            @method('PUT')

            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Plan details</h3>
              </div>
              <div class="card-body">
                <h4 class="mb-3">Basic info</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="name">Plan name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name', $plan->name) }}" required placeholder="e.g. Glow Monthly">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" class="form-control @error('slug') is-invalid @enderror"
                      value="{{ old('slug', $plan->slug) }}" placeholder="Auto from name if empty">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="type">Plan category</label>
                    <input id="type" name="type" type="text" class="form-control @error('type') is-invalid @enderror"
                      value="{{ old('type', $plan->type) }}" placeholder="e.g. Membership">
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                      <option value="active" @selected(old('status', $plan->status) === 'active')>Active</option>
                      <option value="inactive" @selected(old('status', $plan->status) === 'inactive')>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Describe what this plan offers.">{{ old('description', $plan->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Pricing &amp; billing</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="price">Price</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input id="price" name="price" type="number" min="0" step="0.01" required
                        class="form-control @error('price') is-invalid @enderror"
                        value="{{ old('price', $plan->price) }}">
                    </div>
                    @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="billing_cycle">Billing cycle</label>
                    <select id="billing_cycle" name="billing_cycle"
                      class="form-select @error('billing_cycle') is-invalid @enderror">
                      <option value="">Select billing cycle</option>
                      <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle) === 'monthly')>Monthly</option>
                      <option value="quarterly" @selected(old('billing_cycle', $plan->billing_cycle) === 'quarterly')>Quarterly</option>
                      <option value="yearly" @selected(old('billing_cycle', $plan->billing_cycle) === 'yearly')>Yearly</option>
                    </select>
                    @error('billing_cycle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="duration_value">Duration length</label>
                    <input id="duration_value" name="duration_value" type="number" min="1" step="1"
                      class="form-control @error('duration_value') is-invalid @enderror"
                      value="{{ old('duration_value', $plan->duration_value) }}" placeholder="Optional">
                    @error('duration_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="duration_type">Duration unit</label>
                    <select id="duration_type" name="duration_type"
                      class="form-select @error('duration_type') is-invalid @enderror">
                      <option value="">—</option>
                      <option value="month" @selected(old('duration_type', $plan->duration_type) === 'month')>Month</option>
                      <option value="year" @selected(old('duration_type', $plan->duration_type) === 'year')>Year</option>
                    </select>
                    @error('duration_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-hint">Set both length and unit, or leave both empty.</small>
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">
                  Included services <span class="text-secondary fw-normal small">(required)</span>
                </h4>
                <p class="text-secondary small mb-3">Check services from your catalog and set session counts for each.</p>
                <div class="row g-3">
                  <div class="col-12">
                    <span id="included-services-label" class="form-label d-block">Services</span>
                    @if ($services->isEmpty())
                      <p class="text-secondary small mb-0">No services yet. Add services in the catalog first.</p>
                    @else
                      <div id="included-services-list"
                        class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2" role="group"
                        aria-labelledby="included-services-label">
                        @foreach ($services as $svc)
                          <div class="col">
                            <label class="form-check mb-0">
                              <input type="checkbox" class="form-check-input js-included-service-cb"
                                name="included_service_ids[]" value="{{ $svc->id }}" id="edit-inc-svc-{{ $svc->id }}"
                                data-service-label="{{ $svc->name }}"
                                @checked(in_array((string) $svc->id, $selectedIds, true))>
                              <span class="form-check-label">{{ $svc->name }}</span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    @error('included_service_ids')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label">Sessions per selected service</label>
                    <div id="included-service-quantities" class="border rounded p-3 bg-secondary-lt">
                      <span class="text-secondary small">Check at least one service above to set session counts.</span>
                    </div>
                    @error('service_sessions')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Limits &amp; rules</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="max_usage_per_month">Max usage per month</label>
                    <input id="max_usage_per_month" name="max_usage_per_month" type="number" min="0" step="1"
                      class="form-control @error('max_usage_per_month') is-invalid @enderror"
                      value="{{ old('max_usage_per_month', $plan->max_usage_per_month) }}">
                    @error('max_usage_per_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6 d-flex align-items-end">
                    <div class="d-flex flex-column gap-2 mb-2">
                      <label class="form-check">
                        <input type="hidden" name="rollover_unused_sessions" value="0">
                        <input class="form-check-input" type="checkbox" name="rollover_unused_sessions" value="1"
                          @checked((string) old('rollover_unused_sessions', $rollDefault) === '1')>
                        <span class="form-check-label">Rollover unused sessions</span>
                      </label>
                      <label class="form-check">
                        <input type="hidden" name="cancellation_allowed" value="0">
                        <input class="form-check-input" type="checkbox" name="cancellation_allowed" value="1"
                          @checked((string) old('cancellation_allowed', $cancelDefault) === '1')>
                        <span class="form-check-label">Cancellation allowed</span>
                      </label>
                      <label class="form-check">
                        <input type="hidden" name="pause_allowed" value="0">
                        <input class="form-check-input" type="checkbox" name="pause_allowed" value="1"
                          @checked((string) old('pause_allowed', $pauseDefault) === '1')>
                        <span class="form-check-label">Pause allowed</span>
                      </label>
                    </div>
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Care &amp; internal notes</h4>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="terms_and_conditions">Terms &amp; conditions</label>
                    <textarea id="terms_and_conditions" name="terms_and_conditions" rows="3"
                      class="form-control @error('terms_and_conditions') is-invalid @enderror">{{ old('terms_and_conditions', $plan->terms_and_conditions) }}</textarea>
                    @error('terms_and_conditions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="before_care">Before care</label>
                    <textarea id="before_care" name="before_care" rows="3"
                      class="form-control @error('before_care') is-invalid @enderror">{{ old('before_care', $plan->before_care) }}</textarea>
                    @error('before_care') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="aftercare">Aftercare</label>
                    <textarea id="aftercare" name="aftercare" rows="3"
                      class="form-control @error('aftercare') is-invalid @enderror">{{ old('aftercare', $plan->aftercare) }}</textarea>
                    @error('aftercare') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="internal_notes">Internal notes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="3"
                      class="form-control @error('internal_notes') is-invalid @enderror">{{ old('internal_notes', $plan->internal_notes) }}</textarea>
                    @error('internal_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-4">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Checklist</h3>
            </div>
            <div class="card-body">
              <ul class="text-secondary mb-0 ps-3">
                <li>Required: name, price, billing cycle, status, and at least one service with sessions.</li>
                <li>Changing benefits or rules may affect how staff explain the plan to patients.</li>
                <li>Prices use <strong>₱</strong>.</li>
              </ul>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Quick preview</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Billing</div>
                  <div class="datagrid-content">{{ ucfirst(old('billing_cycle', $plan->billing_cycle ?? '—')) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Status</div>
                  <div class="datagrid-content">
                    @php $st = old('status', $plan->status); @endphp
                    @if ($st === 'active')
                      <span class="badge bg-green-lt">Active</span>
                    @else
                      <span class="badge bg-secondary-lt">Inactive</span>
                    @endif
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Price</div>
                  <div class="datagrid-content">₱{{ number_format((float) old('price', $plan->price), 2) }}</div>
                </div>
              </div>
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
      const list = document.getElementById('included-services-list');
      const container = document.getElementById('included-service-quantities');
      if (!list || !container) return;

      function serviceCheckboxes() {
        return list.querySelectorAll('.js-included-service-cb');
      }

      const store = {};
      const serverOld = @json(old('service_sessions', []));
      const fromPlan = @json($serviceSessionsFromPlan ?? []);
      function merge(obj) {
        if (!obj || typeof obj !== 'object' || Array.isArray(obj)) return;
        Object.keys(obj).forEach(function (k) {
          store[String(k)] = String(obj[k]);
        });
      }
      merge(fromPlan);
      merge(serverOld);

      function readInputsIntoStore() {
        container.querySelectorAll('input[type="number"][name^="service_sessions["]').forEach(function (inp) {
          const m = inp.name.match(/^service_sessions\[(\d+)\]$/);
          if (m) store[m[1]] = inp.value;
        });
      }

      function render() {
        readInputsIntoStore();
        container.innerHTML = '';
        const checked = Array.from(serviceCheckboxes()).filter(function (cb) {
          return cb.checked;
        });
        if (checked.length === 0) {
          const span = document.createElement('span');
          span.className = 'text-secondary small';
          span.textContent = 'Check at least one service above to set session counts.';
          container.appendChild(span);
          return;
        }
        checked.forEach(function (cb) {
          const id = String(cb.value);
          const title =
            (cb.getAttribute('data-service-label') || '').trim() || ('Service #' + id);
          const prev = store[id];
          const val = prev !== undefined && prev !== '' ? prev : '1';

          const row = document.createElement('div');
          row.className = 'row g-2 align-items-end mb-2';

          const colLabel = document.createElement('div');
          colLabel.className = 'col-md-7';
          const titleEl = document.createElement('div');
          titleEl.className = 'form-label mb-0';
          titleEl.textContent = title;
          colLabel.appendChild(titleEl);

          const colQty = document.createElement('div');
          colQty.className = 'col-md-5';
          const qtyLabel = document.createElement('label');
          qtyLabel.className = 'form-label';
          qtyLabel.textContent = 'Sessions';
          const input = document.createElement('input');
          input.type = 'number';
          input.className = 'form-control';
          input.name = 'service_sessions[' + id + ']';
          input.min = '1';
          input.step = '1';
          input.value = val;
          colQty.appendChild(qtyLabel);
          colQty.appendChild(input);

          row.appendChild(colLabel);
          row.appendChild(colQty);
          container.appendChild(row);
        });
      }

      list.addEventListener('change', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('js-included-service-cb')) {
          render();
        }
      });

      function boot() {
        if (Array.from(serviceCheckboxes()).some(function (cb) {
          return cb.checked;
        })) {
          render();
        }
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
      } else {
        boot();
      }
    })();
  </script>
@endpush
