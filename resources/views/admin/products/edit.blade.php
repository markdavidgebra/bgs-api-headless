@extends('admin.layouts.master')

@php
  $defaultAssuranceLines = [
      'Curated for post-treatment compatibility',
      'Authentic clinic-sourced inventory',
  ];
  $assuranceForForm = old('showcase_assurance_lines');
  if ($assuranceForForm === null) {
      $rawAssurance = $product->showcase_assurance_lines;
      if ($rawAssurance === null) {
          $assuranceForForm = $defaultAssuranceLines;
      } elseif (is_array($rawAssurance) && $rawAssurance !== []) {
          $assuranceForForm = $rawAssurance;
      } else {
          $assuranceForForm = [''];
      }
  }
  if (! is_array($assuranceForForm)) {
      $assuranceForForm = [''];
  }

  $units = ['pcs', 'bottle', 'box', 'tube', 'set', 'pack'];
  if ($product->unit && ! in_array($product->unit, $units, true)) {
    $units = array_values(array_unique(array_merge([$product->unit], $units)));
  }
  $supplierSuggestions = ['SkinTech Supplies', 'Aesthetic Pro Pharma', 'Glow Clinical Depot', 'DermaSource', 'MediConsumables Inc.', 'Metro Aesthetics Trading'];
  if ($product->supplier && ! in_array($product->supplier, $supplierSuggestions, true)) {
    $supplierSuggestions = array_values(array_unique(array_merge([$product->supplier], $supplierSuggestions)));
  }

  $pageName = old('name', $product->name);
  $planInitial = strtoupper(\Illuminate\Support\Str::substr($pageName, 0, 1));
  [$invLabel, $invBadge] = match ($product->stock_status) {
      'out_of_stock' => ['Out of stock', 'bg-red-lt'],
      'low_stock' => ['Low stock', 'bg-yellow-lt'],
      default => ['In stock', 'bg-green-lt'],
  };
  $saleDefault = $product->is_available_for_sale ? '1' : '0';
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          @if ($product->image_url)
            <span class="avatar avatar-xl rounded" style="background-image: url({{ $product->image_url }})"></span>
          @else
            <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ $planInitial }}</span>
          @endif
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Inventory</div>
          <h2 class="page-title mb-0">{{ $pageName }}</h2>
          <div class="text-secondary small mt-1">
            ID <span class="font-monospace">#{{ $product->id }}</span>
            · <span class="badge {{ $invBadge }}">{{ $invLabel }}</span>
            · Update pricing, stock, and product details on one page.
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.products.show', $product) }}" class="btn">View product</a>
            <a href="{{ route('admin.products') }}" class="btn">Cancel</a>
            <button type="submit" form="product-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if ($errors->any())
        <div class="alert alert-danger" role="alert">
          <div class="fw-bold">We could not save — please review:</div>
          <ul class="mb-0 mt-2 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row g-3">
        <div class="col-lg-8">
          <form id="product-edit-form" method="POST" action="{{ route('admin.products.update', $product) }}"
            enctype="multipart/form-data">
            @csrf

            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Product details</h3>
              </div>
              <div class="card-body">
                <h4 class="mb-3">Basic info</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="name">Product name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name', $product->name) }}" required placeholder="e.g. Hydrating Serum">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" class="form-control @error('slug') is-invalid @enderror"
                      value="{{ old('slug', $product->slug) }}" placeholder="Auto from name if empty">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="category_id">Category</label>
                    <div class="input-group">
                      <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                          <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                      </select>
                      <a href="{{ route('admin.products.categories.create') }}" class="btn" target="_blank" rel="noopener">Add</a>
                    </div>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="brand">Brand</label>
                    <input id="brand" name="brand" type="text" class="form-control @error('brand') is-invalid @enderror"
                      value="{{ old('brand', $product->brand) }}" placeholder="e.g. DermaLab">
                    @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="sku">SKU</label>
                    <input id="sku" name="sku" type="text" class="form-control @error('sku') is-invalid @enderror"
                      value="{{ old('sku', $product->sku) }}" placeholder="e.g. DERM-001-50ML">
                    @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Short description for staff and listings.">{{ old('description', $product->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label">Storefront assurance bullets</label>
                    <div class="text-secondary small mb-2">Shown on the public product page under the description (checkmark list).</div>
                    <div id="showcase-assurance-list">
                      @foreach ($assuranceForForm as $aIdx => $aLine)
                        <div class="input-group showcase-assurance-row mb-2">
                          <span class="input-group-text"><i class="fas fa-check-circle text-secondary" aria-hidden="true"></i></span>
                          <input type="text" name="showcase_assurance_lines[{{ $aIdx }}]"
                            class="form-control @error('showcase_assurance_lines.'.$aIdx) is-invalid @enderror"
                            value="{{ is_string($aLine) ? $aLine : '' }}" maxlength="500" placeholder="e.g. Authentic clinic-sourced inventory">
                          <button type="button" class="btn btn-outline-danger showcase-assurance-remove" title="Remove">&times;</button>
                        </div>
                        @error('showcase_assurance_lines.'.$aIdx)
                          <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                      @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="showcase-assurance-add">Add bullet</button>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Current image</label>
                    @if ($product->image_url)
                      <div class="mb-2 rounded border bg-secondary-lt p-2 d-inline-block">
                        <img src="{{ $product->image_url }}" alt="" class="rounded"
                          style="max-height: 120px; max-width: 100%; object-fit: contain;">
                      </div>
                    @else
                      <p class="text-secondary small mb-2">No image on file.</p>
                    @endif
                    <label class="form-label" for="image">Replace image</label>
                    <input id="image" name="image" type="file" class="form-control @error('image') is-invalid @enderror"
                      accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-hint">Leave empty to keep the current image.</div>
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Pricing</h4>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label required" for="cost_price">Cost price</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input id="cost_price" name="cost_price" type="number" min="0" step="0.01" required
                        class="form-control @error('cost_price') is-invalid @enderror"
                        value="{{ old('cost_price', $product->cost_price) }}">
                    </div>
                    @error('cost_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label required" for="selling_price">Selling price</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input id="selling_price" name="selling_price" type="number" min="0" step="0.01" required
                        class="form-control @error('selling_price') is-invalid @enderror"
                        value="{{ old('selling_price', $product->selling_price) }}">
                    </div>
                    @error('selling_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="discount_mode">Discount (optional)</label>
                    <select id="discount_mode" name="discount_mode"
                      class="form-select mb-2 @error('discount_mode') is-invalid @enderror">
                      <option value="fixed" @selected(old('discount_mode', 'fixed') === 'fixed')>Fixed sale price (₱)</option>
                      <option value="percentage" @selected(old('discount_mode') === 'percentage')>Percentage off selling price</option>
                    </select>
                    @error('discount_mode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div id="discount-fixed-wrap" class="discount-mode-panel @if(old('discount_mode', 'fixed') === 'percentage') d-none @endif">
                      <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input id="discount_price" name="discount_price" type="number" min="0" step="0.01"
                          class="form-control @error('discount_price') is-invalid @enderror"
                          value="{{ old('discount_price', $product->discount_price) }}" placeholder="Sale price"
                          @disabled(old('discount_mode', 'fixed') === 'percentage')>
                      </div>
                      @error('discount_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div id="discount-percent-wrap" class="discount-mode-panel @if(old('discount_mode', 'fixed') !== 'percentage') d-none @endif">
                      <div class="input-group">
                        <input id="discount_percent" name="discount_percent" type="number" min="0" max="100" step="0.01"
                          class="form-control @error('discount_percent') is-invalid @enderror"
                          value="{{ old('discount_percent') }}" placeholder="e.g. 15"
                          @disabled(old('discount_mode', 'fixed') !== 'percentage')>
                        <span class="input-group-text">%</span>
                      </div>
                      <small class="text-secondary d-block mt-1" id="discount-percent-hint"></small>
                      @error('discount_percent') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Inventory</h4>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label required" for="stock_quantity">Stock quantity</label>
                    <input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" required
                      class="form-control @error('stock_quantity') is-invalid @enderror"
                      value="{{ old('stock_quantity', $product->stock_quantity) }}">
                    @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-hint">Changing quantity logs a signed <strong>adjustment</strong> movement.</small>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="minimum_stock_alert">Minimum stock alert</label>
                    <input id="minimum_stock_alert" name="minimum_stock_alert" type="number" min="0" step="1"
                      class="form-control @error('minimum_stock_alert') is-invalid @enderror"
                      value="{{ old('minimum_stock_alert', $product->minimum_stock_alert) }}">
                    @error('minimum_stock_alert') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="unit">Unit</label>
                    <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror">
                      <option value="">Select unit</option>
                      @foreach ($units as $unit)
                        <option value="{{ $unit }}" @selected(old('unit', $product->unit) === $unit)>{{ $unit }}</option>
                      @endforeach
                    </select>
                    @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Status &amp; availability</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="status">Catalog status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                      <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                      <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                      <option value="archived" @selected(old('status', $product->status) === 'archived')>Archived</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6 d-flex align-items-end">
                    <div class="mb-2">
                      <label class="form-check">
                        <input type="hidden" name="is_available_for_sale" value="0">
                        <input class="form-check-input" type="checkbox" name="is_available_for_sale" value="1"
                          @checked((string) old('is_available_for_sale', $saleDefault) === '1')>
                        <span class="form-check-label">Available for sale</span>
                      </label>
                    </div>
                  </div>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Additional info</h4>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label" for="expiry_date">Expiry date</label>
                    <input id="expiry_date" name="expiry_date" type="date"
                      class="form-control @error('expiry_date') is-invalid @enderror"
                      value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}">
                    @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="batch_number">Batch number</label>
                    <input id="batch_number" name="batch_number" type="text"
                      class="form-control @error('batch_number') is-invalid @enderror"
                      value="{{ old('batch_number', $product->batch_number) }}">
                    @error('batch_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="supplier">Supplier</label>
                    <input id="supplier" name="supplier" type="text" class="form-control @error('supplier') is-invalid @enderror"
                      value="{{ old('supplier', $product->supplier) }}" list="product-supplier-options"
                      placeholder="Supplier name">
                    <datalist id="product-supplier-options">
                      @foreach ($supplierSuggestions as $s)
                        <option value="{{ $s }}"></option>
                      @endforeach
                    </datalist>
                    @error('supplier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>
              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Save changes</button>
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
                <li>SKU must stay unique when provided.</li>
                <li>Stock changes create an adjustment row for audit.</li>
                <li>Replace image only when uploading a new file.</li>
              </ul>
            </div>
          </div>
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Snapshot</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Inventory</div>
                  <div class="datagrid-content"><span class="badge {{ $invBadge }}">{{ $invLabel }}</span></div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">On hand (saved)</div>
                  <div class="datagrid-content">{{ number_format((int) $product->stock_quantity) }}
                    {{ $product->unit ?? 'pcs' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Catalog</div>
                  <div class="datagrid-content">{{ ucfirst((string) $product->status) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Sale availability</div>
                  <div class="datagrid-content">{{ $product->is_available_for_sale ? 'Enabled' : 'Disabled' }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Related</h3>
            </div>
            <div class="card-body">
              <a href="{{ route('admin.products.stock-movements') }}" class="btn btn-outline-primary w-100 mb-2">Stock movements log</a>
              <a href="{{ route('admin.products.inventory') }}" class="btn w-100">Inventory monitoring</a>
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
      var mode = document.getElementById('discount_mode');
      var fixedWrap = document.getElementById('discount-fixed-wrap');
      var pctWrap = document.getElementById('discount-percent-wrap');
      var priceInput = document.getElementById('discount_price');
      var pctInput = document.getElementById('discount_percent');
      var sellingInput = document.getElementById('selling_price');
      var hint = document.getElementById('discount-percent-hint');
      if (!mode || !fixedWrap || !pctWrap || !priceInput || !pctInput) return;

      function parseNum(el) {
        var v = parseFloat(el && el.value);
        return isNaN(v) ? 0 : v;
      }

      function updatePercentHint() {
        if (!hint || !sellingInput) return;
        var sell = parseNum(sellingInput);
        var p = parseNum(pctInput);
        if (p <= 0 || sell <= 0) {
          hint.textContent = '';
          return;
        }
        var finalPrice = Math.round(Math.max(0, sell * (1 - p / 100)) * 100) / 100;
        hint.textContent = 'Stored sale price: ₱' + finalPrice.toFixed(2) + ' (from selling price).';
      }

      function sync() {
        var isPct = mode.value === 'percentage';
        fixedWrap.classList.toggle('d-none', isPct);
        pctWrap.classList.toggle('d-none', !isPct);
        priceInput.disabled = isPct;
        pctInput.disabled = !isPct;
        if (isPct) updatePercentHint();
      }

      mode.addEventListener('change', sync);
      pctInput.addEventListener('input', updatePercentHint);
      sellingInput.addEventListener('input', updatePercentHint);
      sync();
    })();

    (function () {
      var list = document.getElementById('showcase-assurance-list');
      if (!list) return;

      function reindex() {
        list.querySelectorAll('.showcase-assurance-row').forEach(function (row, i) {
          var input = row.querySelector('input[type="text"]');
          if (input) input.setAttribute('name', 'showcase_assurance_lines[' + i + ']');
        });
      }

      list.addEventListener('click', function (e) {
        if (!e.target.closest('.showcase-assurance-remove')) return;
        var row = e.target.closest('.showcase-assurance-row');
        if (!row || !list.contains(row)) return;
        var rows = list.querySelectorAll('.showcase-assurance-row');
        if (rows.length <= 1) {
          var inp = row.querySelector('input[type="text"]');
          if (inp) inp.value = '';
          return;
        }
        row.remove();
        reindex();
      });

      document.getElementById('showcase-assurance-add')?.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'input-group showcase-assurance-row mb-2';
        row.innerHTML = '<span class="input-group-text"><i class="fas fa-check-circle text-secondary" aria-hidden="true"></i></span>' +
          '<input type="text" name="showcase_assurance_lines[0]" class="form-control" maxlength="500" placeholder="e.g. Authentic clinic-sourced inventory">' +
          '<button type="button" class="btn btn-outline-danger showcase-assurance-remove" title="Remove">&times;</button>';
        list.appendChild(row);
        reindex();
      });
    })();
  </script>
@endpush
