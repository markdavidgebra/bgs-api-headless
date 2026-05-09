@extends('admin.layouts.master')

@php
  $categoryLabels = [
    'wellness' => 'Wellness',
    'rehab' => 'Rehabilitation',
    'chronic' => 'Chronic care',
  ];
  $expiryLabels = [
    'after_purchase' => 'Starts after purchase',
    'after_first_use' => 'Starts after first use',
  ];
  $selectedIds = collect(
      old('included_service_ids', $package->services->pluck('id')->all()),
  )
      ->map(fn ($v) => (string) $v)
      ->all();
  $serviceSessionsFromPackage = $package->services
      ->mapWithKeys(fn ($s) => [(string) $s->id => (string) $s->pivot->sessions])
      ->all();
  $selectedDoctors = collect(old('assigned_doctors', $package->doctors->pluck('id')->all()))->map(fn ($v) => (string) $v)->all();
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          @if (! empty($package->image_url))
            <span class="avatar avatar-xl rounded" style="background-image: url({{ $package->image_url }})"></span>
          @else
            <span class="avatar avatar-xl rounded bg-azure-lt text-azure">P</span>
          @endif
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Catalog</div>
          <h2 class="page-title mb-0">{{ old('name', $package->name) }}</h2>
          <div class="text-secondary small mt-1">
            Edit package · ID <span class="font-monospace">#{{ $package->id }}</span>
            · {{ $categoryLabels[$package->category ?? ''] ?? ($package->category ?? '—') }}
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.packages.show', $package) }}" class="btn">Cancel</a>
            <button type="submit" form="package-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form id="package-edit-form" method="POST" action="{{ route('admin.packages.update', $package) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Package details</h3>
              </div>
              <div class="card-body">
                <h4 class="mb-3">Basic info</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="name">Package name</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name', $package->name) }}" required>
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="category">Category</label>
                    <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                      <option value="">Select category</option>
                      <option value="wellness" @selected(old('category', $package->category ?? '') === 'wellness')>Wellness
                      </option>
                      <option value="rehab" @selected(old('category', $package->category ?? '') === 'rehab')>Rehabilitation
                      </option>
                      <option value="chronic" @selected(old('category', $package->category ?? '') === 'chronic')>Chronic care
                      </option>
                    </select>
                    @error('category')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                      <option value="active" @selected(old('status', $package->status ?? 'active') === 'active')>Active
                      </option>
                      <option value="inactive" @selected(old('status', $package->status ?? '') === 'inactive')>Inactive
                      </option>
                      <option value="pending" @selected(old('status', $package->status ?? '') === 'pending')>Pending</option>
                      <option value="archived" @selected(old('status', $package->status ?? '') === 'archived')>Archived
                      </option>
                    </select>
                    @error('status')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $package->description) }}</textarea>
                    @error('description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="image">Replace image</label>
                    <input type="file" id="image" name="image" accept="image/*"
                      class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-hint mt-2">Leave empty to keep current image.</div>
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">
                  Included services <span class="text-secondary fw-normal small">(required)</span>
                </h4>
                <p class="text-secondary small mb-3">Check the services to include, then set the number of sessions for
                  each.</p>
                <div class="row g-3">
                  <div class="col-12">
                    <span id="edit-included-services-label" class="form-label d-block">Services</span>
                    @if ($services->isEmpty())
                      <p class="text-secondary small mb-0">No services in the catalog yet.</p>
                    @else
                      <div id="included-services-list"
                        class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2" role="group"
                        aria-labelledby="edit-included-services-label">
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

                <h4 class="mb-3">Pricing</h4>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label required" for="total_price">Total price</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="number" id="total_price" name="total_price" min="0" step="0.01" required
                        class="form-control @error('total_price') is-invalid @enderror"
                        value="{{ old('total_price', $package->price) }}">
                    </div>
                    @error('total_price')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="original_price">Original price</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="number" id="original_price" name="original_price" min="0" step="0.01"
                        class="form-control @error('original_price') is-invalid @enderror"
                        value="{{ old('original_price', $package->original_price) }}" placeholder="Optional">
                    </div>
                    @error('original_price')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="discount_percent">Discount (%)</label>
                    <input type="number" id="discount_percent" name="discount_percent" min="0" max="100" step="0.01"
                      class="form-control @error('discount_percent') is-invalid @enderror"
                      value="{{ old('discount_percent', $package->discount_percent) }}" placeholder="Optional">
                    @error('discount_percent')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <h4 class="mb-3 mt-4">Validity</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="validity_duration">Duration</label>
                    <div class="input-group">
                      <input type="number" id="validity_duration" name="validity_duration" min="1" step="1"
                        class="form-control @error('validity_duration') is-invalid @enderror"
                        value="{{ old('validity_duration', $package->validity_value) }}" placeholder="e.g. 30">
                      <select class="form-select @error('validity_unit') is-invalid @enderror" id="validity_unit"
                        name="validity_unit" style="max-width: 8rem;">
                        <option value="days" @selected(old('validity_unit', $package->validity_type ?? 'days') === 'days')>Days
                        </option>
                        <option value="months" @selected(old('validity_unit', $package->validity_type ?? '') === 'months')>
                          Months</option>
                        <option value="years" @selected(old('validity_unit', $package->validity_type ?? '') === 'years')>Years
                        </option>
                      </select>
                    </div>
                    @error('validity_duration')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('validity_unit')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="expiry_rule">Expiry rule</label>
                    <select class="form-select @error('expiry_rule') is-invalid @enderror" id="expiry_rule" name="expiry_rule">
                      <option value="after_purchase" @selected(old('expiry_rule', $package->expiry_rule ?? 'after_purchase') === 'after_purchase')>
                        {{ $expiryLabels['after_purchase'] }}</option>
                      <option value="after_first_use" @selected(old('expiry_rule', $package->expiry_rule ?? '') === 'after_first_use')>
                        {{ $expiryLabels['after_first_use'] }}</option>
                    </select>
                    @error('expiry_rule')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Limits &amp; staff</h4>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label" for="max_usage_per_day">Max usage per day</label>
                    <input type="number" id="max_usage_per_day" name="max_usage_per_day" min="1" step="1"
                      class="form-control @error('max_usage_per_day') is-invalid @enderror"
                      value="{{ old('max_usage_per_day', $package->max_usage_per_day) }}" placeholder="e.g. 1">
                    @error('max_usage_per_day')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4 d-flex align-items-end">
                    <label class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="allow_sharing" value="1"
                        @checked(old('allow_sharing', $package->allow_sharing))>
                      <span class="form-check-label">Allow sharing</span>
                    </label>
                  </div>
                  <div class="col-md-4 d-flex align-items-end">
                    <label class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="refundable" value="1"
                        @checked(old('refundable', $package->refundable))>
                      <span class="form-check-label">Refundable</span>
                    </label>
                  </div>
                  <div class="col-12">
                    <span id="edit-assigned-doctors-label" class="form-label d-block">Assigned doctors</span>
                    @if ($doctors->isEmpty())
                      <p class="text-secondary small mb-0">No doctors in the system yet.</p>
                    @else
                      <div
                        class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2 @error('assigned_doctors') border-danger @enderror"
                        role="group" aria-labelledby="edit-assigned-doctors-label">
                        @foreach ($doctors as $doc)
                          <div class="col">
                            <label class="form-check mb-0">
                              <input type="checkbox" class="form-check-input" name="assigned_doctors[]"
                                value="{{ $doc->id }}" id="edit-assigned-doctor-{{ $doc->id }}"
                                @checked(in_array((string) $doc->id, $selectedDoctors, true))>
                              <span class="form-check-label">{{ $doc->name }}</span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    <small class="form-hint">Optional — check all doctors who may deliver this package.</small>
                    @error('assigned_doctors')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Care &amp; internal notes</h4>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="before_care">Before care</label>
                    <textarea id="before_care" name="before_care" rows="3"
                      class="form-control @error('before_care') is-invalid @enderror">{{ old('before_care', $package->before_care) }}</textarea>
                    @error('before_care')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="aftercare">Aftercare</label>
                    <textarea id="aftercare" name="aftercare" rows="3"
                      class="form-control @error('aftercare') is-invalid @enderror">{{ old('aftercare', $package->aftercare) }}</textarea>
                    @error('aftercare')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $package->notes) }}</textarea>
                    @error('notes')
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
              <h3 class="card-title">Quick preview</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-3">
                <div class="datagrid-item">
                  <div class="datagrid-title">Status</div>
                  <div class="datagrid-content">
                    @if (old('status', $package->status ?? 'active') === 'active')
                      <span class="badge bg-green-lt">Active</span>
                    @else
                      <span class="badge bg-secondary-lt">Inactive</span>
                    @endif
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Total price</div>
                  <div class="datagrid-content fw-semibold">₱ {{ number_format((float) old('total_price', $package->price), 2) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Expiry rule</div>
                  <div class="datagrid-content">
                    @php $er = old('expiry_rule', $package->expiry_rule ?? 'after_purchase'); @endphp
                    {{ $expiryLabels[$er] ?? $er }}
                  </div>
                </div>
              </div>
              <h4 class="card-title">Tips</h4>
              <ul class="text-secondary mb-0 ps-3">
                <li>Included services set sessions per service.</li>
                <li>Use original price with discount to show savings.</li>
                <li>Leave image empty to keep the current image.</li>
              </ul>
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
      const fromPackage = @json($serviceSessionsFromPackage ?? []);
      function merge(obj) {
        if (!obj || typeof obj !== 'object' || Array.isArray(obj)) return;
        Object.keys(obj).forEach(function (k) {
          store[String(k)] = String(obj[k]);
        });
      }
      merge(fromPackage);
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
