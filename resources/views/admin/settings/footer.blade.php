@extends('admin.layouts.master')

@section('content')
  @php($socialLinks = old('social_links', $footer['social_links'] ?? []))
  @if (! is_array($socialLinks))
    @php($socialLinks = [])
  @endif
  @php($socialLinks = collect($socialLinks)->map(fn ($row) => [
      'icon' => trim((string) data_get($row, 'icon', '')),
      'url' => trim((string) data_get($row, 'url', '')),
  ])->values()->all())
  @if (count($socialLinks) === 0)
    @php($socialLinks = [['icon' => '', 'url' => '']])
  @endif

  @php($departmentLinks = old('department_links', $footer['department_links'] ?? []))
  @if (! is_array($departmentLinks))
    @php($departmentLinks = [])
  @endif
  @php($departmentLinks = collect($departmentLinks)->map(fn ($row) => [
      'label' => trim((string) data_get($row, 'label', '')),
      'url' => trim((string) data_get($row, 'url', '')),
  ])->values()->all())
  @if (count($departmentLinks) === 0)
    @php($departmentLinks = [['label' => '', 'url' => '']])
  @endif

  @php($pageLinks = old('page_links', $footer['page_links'] ?? []))
  @if (! is_array($pageLinks))
    @php($pageLinks = [])
  @endif
  @php($pageLinks = collect($pageLinks)->map(fn ($row) => [
      'label' => trim((string) data_get($row, 'label', '')),
      'url' => trim((string) data_get($row, 'url', '')),
  ])->values()->all())
  @if (count($pageLinks) === 0)
    @php($pageLinks = [['label' => '', 'url' => '']])
  @endif

  @php($bottomLinks = old('bottom_links', $footer['bottom_links'] ?? []))
  @if (! is_array($bottomLinks))
    @php($bottomLinks = [])
  @endif
  @php($bottomLinks = collect($bottomLinks)->map(fn ($row) => [
      'label' => trim((string) data_get($row, 'label', '')),
      'url' => trim((string) data_get($row, 'url', '')),
  ])->values()->all())
  @if (count($bottomLinks) === 0)
    @php($bottomLinks = [['label' => '', 'url' => '']])
  @endif

  @php($socialIconOptions = [
      'facebook' => 'Facebook',
      'twitter' => 'Twitter / X',
      'instagram' => 'Instagram',
      'pinterest' => 'Pinterest',
      'linkedin' => 'LinkedIn',
      'youtube' => 'YouTube',
  ])

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Settings</div>
          <h2 class="page-title mb-0">Site footer</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.settings') }}" class="btn">Back to settings</a>
            <button type="submit" form="footer-settings-form" class="btn btn-primary">Save</button>
          </div>
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

      <form id="footer-settings-form" method="POST" action="{{ route('admin.settings.footer.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Newsletter block</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="newsletter_title">Title</label>
                    <textarea id="newsletter_title" name="newsletter_title" rows="3"
                      class="form-control @error('newsletter_title') is-invalid @enderror"
                      placeholder="Line breaks become separate lines on the site.">{{ old('newsletter_title', $footer['newsletter_title'] ?? '') }}</textarea>
                    <div class="form-hint">Use a new line for the second line (replaces the old &lt;br&gt;).</div>
                    @error('newsletter_title')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="newsletter_email_placeholder">Email field placeholder</label>
                    <input id="newsletter_email_placeholder" type="text" name="newsletter_email_placeholder"
                      class="form-control @error('newsletter_email_placeholder') is-invalid @enderror"
                      value="{{ old('newsletter_email_placeholder', $footer['newsletter_email_placeholder'] ?? '') }}">
                    @error('newsletter_email_placeholder')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="newsletter_blurb">Short text below the form</label>
                    <textarea id="newsletter_blurb" name="newsletter_blurb" rows="4"
                      class="form-control @error('newsletter_blurb') is-invalid @enderror">{{ old('newsletter_blurb', $footer['newsletter_blurb'] ?? '') }}</textarea>
                    @error('newsletter_blurb')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Social links</h3>
              </div>
              <div class="card-body">
                <div id="social-links-list" class="d-flex flex-column gap-2">
                  @foreach ($socialLinks as $idx => $row)
                    <div class="row g-2 align-items-end social-link-row">
                      <div class="col-md-3">
                        <label class="form-label">Icon</label>
                        <select name="social_links[{{ $idx }}][icon]" class="form-select">
                          <option value="">—</option>
                          @foreach ($socialIconOptions as $val => $label)
                            <option value="{{ $val }}" @selected($row['icon'] === $val)>{{ $label }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-8">
                        <label class="form-label">URL</label>
                        <input type="text" name="social_links[{{ $idx }}][url]" class="form-control"
                          placeholder="/contact or https://..." value="{{ $row['url'] }}">
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger w-100 remove-social-row" title="Remove">&times;</button>
                      </div>
                    </div>
                  @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" id="add-social-row">Add social link</button>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Department column</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label" for="department_title">Column title</label>
                  <input id="department_title" type="text" name="department_title"
                    class="form-control @error('department_title') is-invalid @enderror"
                    value="{{ old('department_title', $footer['department_title'] ?? '') }}">
                  @error('department_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div id="department-links-list" class="d-flex flex-column gap-2">
                  @foreach ($departmentLinks as $idx => $row)
                    <div class="row g-2 align-items-end department-link-row">
                      <div class="col-md-5">
                        <label class="form-label">Label</label>
                        <input type="text" name="department_links[{{ $idx }}][label]" class="form-control"
                          value="{{ $row['label'] }}">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">URL</label>
                        <input type="text" name="department_links[{{ $idx }}][url]" class="form-control"
                          value="{{ $row['url'] }}">
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger w-100 remove-department-row" title="Remove">&times;</button>
                      </div>
                    </div>
                  @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" id="add-department-row">Add link</button>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Contact column</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label" for="contact_title">Column title</label>
                  <input id="contact_title" type="text" name="contact_title"
                    class="form-control @error('contact_title') is-invalid @enderror"
                    value="{{ old('contact_title', $footer['contact_title'] ?? '') }}">
                  @error('contact_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="contact_address_label">Address label</label>
                    <input id="contact_address_label" type="text" name="contact_address_label" class="form-control"
                      value="{{ old('contact_address_label', $footer['contact_address_label'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact_address">Address</label>
                    <input id="contact_address" type="text" name="contact_address" class="form-control"
                      value="{{ old('contact_address', $footer['contact_address'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact_phone_label">Phone label</label>
                    <input id="contact_phone_label" type="text" name="contact_phone_label" class="form-control"
                      value="{{ old('contact_phone_label', $footer['contact_phone_label'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact_phone">Phone (display)</label>
                    <input id="contact_phone" type="text" name="contact_phone" class="form-control"
                      value="{{ old('contact_phone', $footer['contact_phone'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact_email_label">Email label</label>
                    <input id="contact_email_label" type="text" name="contact_email_label" class="form-control"
                      value="{{ old('contact_email_label', $footer['contact_email_label'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact_email">Email</label>
                    <input id="contact_email" type="text" name="contact_email" class="form-control"
                      value="{{ old('contact_email', $footer['contact_email'] ?? '') }}">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Page links column</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label" for="page_links_title">Column title</label>
                  <input id="page_links_title" type="text" name="page_links_title"
                    class="form-control @error('page_links_title') is-invalid @enderror"
                    value="{{ old('page_links_title', $footer['page_links_title'] ?? '') }}">
                  @error('page_links_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div id="page-links-list" class="d-flex flex-column gap-2">
                  @foreach ($pageLinks as $idx => $row)
                    <div class="row g-2 align-items-end page-link-row">
                      <div class="col-md-5">
                        <label class="form-label">Label</label>
                        <input type="text" name="page_links[{{ $idx }}][label]" class="form-control"
                          value="{{ $row['label'] }}">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">URL</label>
                        <input type="text" name="page_links[{{ $idx }}][url]" class="form-control"
                          value="{{ $row['url'] }}">
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger w-100 remove-page-row" title="Remove">&times;</button>
                      </div>
                    </div>
                  @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" id="add-page-row">Add link</button>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Copyright &amp; bottom bar</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="copyright_brand">Brand name</label>
                    <input id="copyright_brand" type="text" name="copyright_brand" class="form-control"
                      value="{{ old('copyright_brand', $footer['copyright_brand'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="copyright_brand_url">Brand link URL</label>
                    <input id="copyright_brand_url" type="text" name="copyright_brand_url" class="form-control"
                      placeholder="/about-us"
                      value="{{ old('copyright_brand_url', $footer['copyright_brand_url'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="copyright_year">Year</label>
                    <input id="copyright_year" type="text" name="copyright_year" class="form-control"
                      value="{{ old('copyright_year', $footer['copyright_year'] ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="copyright_suffix">Suffix (e.g. All Rights Reserved)</label>
                    <input id="copyright_suffix" type="text" name="copyright_suffix" class="form-control"
                      value="{{ old('copyright_suffix', $footer['copyright_suffix'] ?? '') }}">
                  </div>
                </div>
                <hr class="my-3">
                <label class="form-label">Bottom links</label>
                <div id="bottom-links-list" class="d-flex flex-column gap-2">
                  @foreach ($bottomLinks as $idx => $row)
                    <div class="row g-2 align-items-end bottom-link-row">
                      <div class="col-md-5">
                        <label class="form-label">Label</label>
                        <input type="text" name="bottom_links[{{ $idx }}][label]" class="form-control"
                          value="{{ $row['label'] }}">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">URL</label>
                        <input type="text" name="bottom_links[{{ $idx }}][url]" class="form-control"
                          value="{{ $row['url'] }}">
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger w-100 remove-bottom-row" title="Remove">&times;</button>
                      </div>
                    </div>
                  @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" id="add-bottom-row">Add link</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <template id="tpl-social-row">
    <div class="row g-2 align-items-end social-link-row">
      <div class="col-md-3">
        <label class="form-label">Icon</label>
        <select name="social_links[__I__][icon]" class="form-select">
          <option value="">—</option>
          @foreach ($socialIconOptions as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label">URL</label>
        <input type="text" name="social_links[__I__][url]" class="form-control" placeholder="/contact or https://...">
      </div>
      <div class="col-md-1">
        <button type="button" class="btn btn-outline-danger w-100 remove-social-row" title="Remove">&times;</button>
      </div>
    </div>
  </template>
  <template id="tpl-link-row">
    <div class="row g-2 align-items-end __CLASS__">
      <div class="col-md-5">
        <label class="form-label">Label</label>
        <input type="text" name="__NAME__[__I__][label]" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">URL</label>
        <input type="text" name="__NAME__[__I__][url]" class="form-control">
      </div>
      <div class="col-md-1">
        <button type="button" class="btn btn-outline-danger w-100 __REMOVE__" title="Remove">&times;</button>
      </div>
    </div>
  </template>

  @push('scripts')
    <script>
      (function () {
        function reindex(container, rowClass) {
          container.querySelectorAll('.' + rowClass).forEach(function (row, i) {
            row.querySelectorAll('[name]').forEach(function (el) {
              var n = el.getAttribute('name');
              if (!n) return;
              el.setAttribute('name', n.replace(/\[\d+\]/, '[' + i + ']'));
            });
          });
        }

        function bindRemove(container, rowSelector, rowClass, removeBtnClass) {
          container.addEventListener('click', function (e) {
            if (!e.target.closest('.' + removeBtnClass)) return;
            var row = e.target.closest(rowSelector);
            if (!row || !container.contains(row)) return;
            row.remove();
            reindex(container, rowClass);
          });
        }

        function addFromTemplate(tplId, listId, rowClass) {
          var tpl = document.getElementById(tplId);
          var list = document.getElementById(listId);
          if (!tpl || !list) return;
          var html = tpl.innerHTML.replace(/__I__/g, list.querySelectorAll('.' + rowClass).length);
          var wrap = document.createElement('div');
          wrap.innerHTML = html.trim();
          list.appendChild(wrap.firstElementChild);
          reindex(list, rowClass);
        }

        var socialList = document.getElementById('social-links-list');
        if (socialList) {
          bindRemove(socialList, '.social-link-row', 'social-link-row', 'remove-social-row');
        }
        document.getElementById('add-social-row')?.addEventListener('click', function () {
          addFromTemplate('tpl-social-row', 'social-links-list', 'social-link-row');
        });

        function bindLinkList(listId, addId, tplId, rowClass, removeClass, fieldName) {
          var list = document.getElementById(listId);
          if (!list) return;
          bindRemove(list, '.' + rowClass, rowClass, removeClass);
          document.getElementById(addId)?.addEventListener('click', function () {
            var tpl = document.getElementById(tplId);
            if (!tpl) return;
            var i = list.querySelectorAll('.' + rowClass).length;
            var html = tpl.innerHTML
              .replace(/__I__/g, i)
              .replace(/__NAME__/g, fieldName)
              .replace(/__CLASS__/g, rowClass)
              .replace(/__REMOVE__/g, removeClass);
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            list.appendChild(wrap.firstElementChild);
            reindex(list, rowClass);
          });
        }

        bindLinkList('department-links-list', 'add-department-row', 'tpl-link-row', 'department-link-row', 'remove-department-row', 'department_links');
        bindLinkList('page-links-list', 'add-page-row', 'tpl-link-row', 'page-link-row', 'remove-page-row', 'page_links');
        bindLinkList('bottom-links-list', 'add-bottom-row', 'tpl-link-row', 'bottom-link-row', 'remove-bottom-row', 'bottom_links');
      })();
    </script>
  @endpush
@endsection
