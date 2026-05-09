@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Products</div>
          <h2 class="page-title mb-0">Catalog page</h2>
          <div class="text-secondary small mt-1">Intro copy and trust highlights on <code>/our-products</code>.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.products') }}" class="btn">All products</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.products.pages.update') }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">Hero text</h3>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="tagline">Tagline</label>
              <input id="tagline" type="text" name="tagline" class="form-control @error('tagline') is-invalid @enderror"
                value="{{ $tagline }}" maxlength="255" required>
              <div class="form-hint">Small line above the main heading (e.g. section label).</div>
              @error('tagline')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <label class="form-label" for="heading">Main heading</label>
              <textarea id="heading" name="heading" rows="3" class="form-control @error('heading') is-invalid @enderror"
                required maxlength="1000">{{ $heading }}</textarea>
              <div class="form-hint">Use line breaks where you want a new line in the title.</div>
              @error('heading')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-0">
              <label class="form-label" for="lede">Intro paragraph</label>
              <textarea id="lede" name="lede" rows="4" class="form-control @error('lede') is-invalid @enderror"
                required maxlength="5000">{{ $lede }}</textarea>
              @error('lede')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">Trust highlights</h3>
            <div class="card-subtitle text-secondary">Icon + label row under the intro (Font Awesome 5 solid).</div>
          </div>
          <div class="card-body">
            <div id="trust-items-list" data-default-icon="{{ $defaultTrustIcon }}">
              @foreach ($trustItems as $idx => $row)
                <div class="trust-item-row border rounded p-3 mb-2">
                  <div class="row g-3 align-items-end">
                    <div class="col-12">
                      <label class="form-label mb-2">Icon</label>
                      <input type="hidden" name="trust_items[{{ $idx }}][icon]" value="{{ $row['icon'] ?? $defaultTrustIcon }}"
                        class="trust-icon-value @error('trust_items.'.$idx.'.icon') is-invalid @enderror">
                      <div class="trust-icon-picker d-flex flex-wrap gap-1">
                        @foreach ($iconOptions as $value => $optLabel)
                          @if ($value === 'custom')
                            <button type="button" class="btn btn-sm btn-outline-secondary trust-icon-pick"
                              data-value="custom" title="Custom class (type below)">
                              <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                            </button>
                          @else
                            <button type="button" class="btn btn-sm btn-outline-secondary trust-icon-pick"
                              data-value="{{ $value }}" title="{{ $optLabel }}">
                              <i class="fas {{ $value }}" aria-hidden="true"></i>
                            </button>
                          @endif
                        @endforeach
                      </div>
                      @error('trust_items.'.$idx.'.icon')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                      @enderror
                    </div>
                    <div
                      class="col-md-6 trust-custom-wrap {{ ($row['icon'] ?? '') === 'custom' ? '' : 'd-none' }}">
                      <label class="form-label">Custom class</label>
                      <input type="text" name="trust_items[{{ $idx }}][icon_custom]"
                        class="form-control trust-custom-input @error('trust_items.'.$idx.'.icon_custom') is-invalid @enderror"
                        value="{{ $row['icon_custom'] ?? '' }}" placeholder="fa-heart" maxlength="80"
                        autocomplete="off">
                      <div class="form-hint">Include the <code>fa-</code> prefix (solid set).</div>
                      @error('trust_items.'.$idx.'.icon_custom')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Label</label>
                      <input type="text" name="trust_items[{{ $idx }}][label]"
                        class="form-control trust-label-input @error('trust_items.'.$idx.'.label') is-invalid @enderror"
                        value="{{ $row['label'] ?? '' }}" maxlength="255" required>
                      @error('trust_items.'.$idx.'.label')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto text-md-end">
                      <button type="button" class="btn btn-outline-danger trust-remove-row" title="Remove">&times;</button>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            <button type="button" class="btn btn-outline-primary" id="trust-add-row">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
              </svg>
              Add item
            </button>
          </div>
        </div>

        <div class="text-end mb-4">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .trust-icon-picker .trust-icon-pick {
      width: 2.25rem;
      height: 2.25rem;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .trust-icon-picker .trust-icon-pick i {
      font-size: 1rem;
      line-height: 1;
    }
  </style>
@endpush

@push('scripts')
  <script>
    (function () {
      function reindexTrustRows() {
        var list = document.getElementById('trust-items-list');
        if (!list) return;
        list.querySelectorAll('.trust-item-row').forEach(function (row, i) {
          row.querySelectorAll('[name]').forEach(function (el) {
            var n = el.getAttribute('name');
            if (!n) return;
            el.setAttribute('name', n.replace(/\[\d+\]/, '[' + i + ']'));
          });
        });
      }

      function defaultTrustIcon() {
        var list = document.getElementById('trust-items-list');
        return (list && list.getAttribute('data-default-icon')) || 'fa-leaf-heart';
      }

      function toggleCustomWrapForValue(row, value) {
        var wrap = row.querySelector('.trust-custom-wrap');
        if (!wrap) return;
        if (value === 'custom') {
          wrap.classList.remove('d-none');
        } else {
          wrap.classList.add('d-none');
        }
      }

      function updateTrustIconButtons(row) {
        var hidden = row.querySelector('.trust-icon-value');
        if (!hidden) return;
        var v = hidden.value;
        row.querySelectorAll('.trust-icon-pick').forEach(function (btn) {
          var on = btn.getAttribute('data-value') === v;
          btn.classList.toggle('btn-primary', on);
          btn.classList.toggle('btn-outline-secondary', !on);
        });
      }

      function setTrustIcon(row, value) {
        var hidden = row.querySelector('.trust-icon-value');
        if (!hidden) return;
        hidden.value = value;
        toggleCustomWrapForValue(row, value);
        updateTrustIconButtons(row);
      }

      var list = document.getElementById('trust-items-list');
      if (list) {
        list.querySelectorAll('.trust-item-row').forEach(function (row) {
          updateTrustIconButtons(row);
          var hidden = row.querySelector('.trust-icon-value');
          if (hidden) {
            toggleCustomWrapForValue(row, hidden.value);
          }
        });

        list.addEventListener('click', function (e) {
          var pick = e.target.closest('.trust-icon-pick');
          if (pick && list.contains(pick.closest('.trust-item-row'))) {
            var row = pick.closest('.trust-item-row');
            setTrustIcon(row, pick.getAttribute('data-value'));
            return;
          }

          if (!e.target.closest('.trust-remove-row')) return;
          var row = e.target.closest('.trust-item-row');
          if (!row || !list.contains(row)) return;
          var rows = list.querySelectorAll('.trust-item-row');
          if (rows.length <= 1) {
            var inp = row.querySelector('.trust-label-input');
            var cust = row.querySelector('.trust-custom-input');
            if (inp) inp.value = '';
            if (cust) cust.value = '';
            setTrustIcon(row, defaultTrustIcon());
            return;
          }
          row.remove();
          reindexTrustRows();
        });
      }

      document.getElementById('trust-add-row')?.addEventListener('click', function () {
        if (!list) return;
        var rows = list.querySelectorAll('.trust-item-row');
        var proto = rows[rows.length - 1];
        if (!proto) return;
        var row = proto.cloneNode(true);
        var labelInp = row.querySelector('.trust-label-input');
        var cust = row.querySelector('.trust-custom-input');
        if (labelInp) labelInp.value = '';
        if (cust) cust.value = '';
        row.querySelectorAll('.is-invalid').forEach(function (el) {
          el.classList.remove('is-invalid');
        });
        list.appendChild(row);
        reindexTrustRows();
        setTrustIcon(row, defaultTrustIcon());
      });
    })();
  </script>
@endpush
