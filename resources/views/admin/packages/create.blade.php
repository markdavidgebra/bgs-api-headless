@extends('admin.layouts.master')

@php
  $expiryLabels = [
    'after_purchase' => 'Starts after purchase',
    'after_first_use' => 'Starts after first use',
  ];
  $oldIds = (array) old('included_service_ids', []);
  $oldDocs = (array) old('assigned_clinical_staff', []);
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">P</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Catalog</div>
          <h2 class="page-title mb-0">{{ old('name', 'New package') }}</h2>
          <div class="text-secondary small mt-1">Create a treatment bundle with services, pricing, and rules.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.packages') }}" class="btn">Cancel</a>
            <button type="submit" form="package-create-form" class="btn btn-primary">Save package</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form id="package-create-form" method="POST" action="{{ route('admin.packages.store') }}"
            enctype="multipart/form-data">
            @csrf
            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Package details</h3>
              </div>
              <div class="card-body">
                <h4 class="mb-3">Basic info</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="package_name">Package name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="package_name"
                      name="name" value="{{ old('name') }}" required placeholder="e.g. Gold glow package">
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="category">Category</label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                      <option value="">Select category</option>
                      <option value="wellness" @selected(old('category') === 'wellness')>Wellness</option>
                      <option value="rehab" @selected(old('category') === 'rehab')>Rehabilitation</option>
                      <option value="chronic" @selected(old('category') === 'chronic')>Chronic care</option>
                    </select>
                    @error('category')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                      <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                      <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                      <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                      <option value="archived" @selected(old('status') === 'archived')>Archived</option>
                    </select>
                    @error('status')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                      name="description" rows="4" placeholder="What patients get and why it’s valuable">{{ old('description') }}</textarea>
                    @error('description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="image">Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image"
                      accept="image/*">
                    @error('image')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    <span id="included-services-label" class="form-label d-block">Services</span>
                    @if ($services->isEmpty())
                      <p class="text-secondary small mb-0">No services in the catalog yet. Add services first.</p>
                    @else
                      <div id="included-services-list"
                        class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2"
                        role="group"
                        aria-labelledby="included-services-label">
                        @foreach ($services as $svc)
                          <div class="col">
                            <label class="form-check mb-0">
                              <input type="checkbox" class="form-check-input js-included-service-cb"
                                name="included_service_ids[]" value="{{ $svc->id }}" id="inc-svc-{{ $svc->id }}"
                                data-service-label="{{ $svc->name }}"
                                @checked(in_array((string) $svc->id, $oldIds, true))>
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
                      <input type="number" class="form-control @error('total_price') is-invalid @enderror" id="total_price"
                        name="total_price" value="{{ old('total_price') }}" min="0" step="0.01" required placeholder="0.00">
                    </div>
                    @error('total_price')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="original_price">Original price</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="number" class="form-control @error('original_price') is-invalid @enderror"
                        id="original_price" name="original_price" value="{{ old('original_price') }}" min="0" step="0.01"
                        placeholder="Optional">
                    </div>
                    @error('original_price')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="discount_percent">Discount (%)</label>
                    <input type="number" class="form-control @error('discount_percent') is-invalid @enderror"
                      id="discount_percent" name="discount_percent" value="{{ old('discount_percent') }}" min="0"
                      max="100" step="0.01" placeholder="Optional">
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
                      <input type="number" class="form-control @error('validity_duration') is-invalid @enderror"
                        id="validity_duration" name="validity_duration" value="{{ old('validity_duration') }}" min="1"
                        step="1" placeholder="e.g. 30">
                      <select class="form-select @error('validity_unit') is-invalid @enderror" id="validity_unit"
                        name="validity_unit" style="max-width: 8rem;">
                        <option value="days" @selected(old('validity_unit', 'days') === 'days')>Days</option>
                        <option value="months" @selected(old('validity_unit') === 'months')>Months</option>
                        <option value="years" @selected(old('validity_unit') === 'years')>Years</option>
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
                    <select class="form-select @error('expiry_rule') is-invalid @enderror" id="expiry_rule"
                      name="expiry_rule">
                      <option value="after_purchase" @selected(old('expiry_rule', 'after_purchase') === 'after_purchase')>
                        {{ $expiryLabels['after_purchase'] }}
                      </option>
                      <option value="after_first_use" @selected(old('expiry_rule') === 'after_first_use')>
                        {{ $expiryLabels['after_first_use'] }}
                      </option>
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
                    <input type="number" class="form-control @error('max_usage_per_day') is-invalid @enderror"
                      id="max_usage_per_day" name="max_usage_per_day" value="{{ old('max_usage_per_day') }}" min="1"
                      step="1" placeholder="e.g. 1">
                    @error('max_usage_per_day')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4 d-flex align-items-end">
                    <label class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="allow_sharing" value="1"
                        @checked(old('allow_sharing'))>
                      <span class="form-check-label">Allow sharing</span>
                    </label>
                  </div>
                  <div class="col-md-4 d-flex align-items-end">
                    <label class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="refundable" value="1"
                        @checked(old('refundable'))>
                      <span class="form-check-label">Refundable</span>
                    </label>
                  </div>
                  <div class="col-12">
                    <span id="assigned-doctors-label" class="form-label d-block">Assigned doctors</span>
                    @if ($clinicalStaff->isEmpty())
                      <p class="text-secondary small mb-0">No doctors in the system yet.</p>
                    @else
                      <div
                        class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2 @error('assigned_clinical_staff') border-danger @enderror"
                        role="group" aria-labelledby="assigned-doctors-label">
                        @foreach ($clinicalStaff as $doc)
                          <div class="col">
                            <label class="form-check mb-0">
                              <input type="checkbox" class="form-check-input" name="assigned_clinical_staff[]"
                                value="{{ $doc->id }}" id="assigned-doctor-{{ $doc->id }}"
                                @checked(in_array((string) $doc->id, $oldDocs, true))>
                              <span class="form-check-label">{{ $doc->name }}</span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    <small class="form-hint">Optional — check all doctors who may deliver this package.</small>
                    @error('assigned_clinical_staff')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Care &amp; internal notes</h4>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="before_care">Before care</label>
                    <textarea class="form-control @error('before_care') is-invalid @enderror" id="before_care"
                      name="before_care" rows="3" placeholder="Instructions before treatment">{{ old('before_care') }}</textarea>
                    @error('before_care')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="aftercare">Aftercare</label>
                    <textarea class="form-control @error('aftercare') is-invalid @enderror" id="aftercare" name="aftercare"
                      rows="3" placeholder="Instructions after treatment">{{ old('aftercare') }}</textarea>
                    @error('aftercare')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                      placeholder="Internal notes">{{ old('notes') }}</textarea>
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
              <h3 class="card-title">Checklist</h3>
            </div>
            <div class="card-body">
              <ul class="text-secondary mb-0 ps-3">
                <li>Required: name, total price, and at least one service with sessions.</li>
                <li>Optional image, validity, and discount (pair with original price to show savings).</li>
                <li>Prices use <strong>₱</strong>.</li>
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
      const serverSessions = @json(old('service_sessions', []));
      if (serverSessions && typeof serverSessions === 'object' && !Array.isArray(serverSessions)) {
        Object.keys(serverSessions).forEach(function (k) {
          store[k] = String(serverSessions[k]);
        });
      }

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
          const id = cb.value;
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
