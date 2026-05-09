@php
  $isEdit = $slide !== null;
  $o = function (string $key, $default = '') use ($slide) {
      if (! $slide) {
          return old($key, $default);
      }

      return old($key, data_get($slide, $key) ?? $default);
  };
@endphp

{{-- Main copy: top-to-bottom as it reads on the site --}}
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <h3 class="card-title mb-3">Slide text</h3>
    <p class="text-secondary small mb-4">Type normally. Use <kbd>Enter</kbd> in the headline or paragraph when you want a new line.</p>

    <div class="mb-3">
      <label class="form-label" for="subtitle">Small line above the headline</label>
      <input id="subtitle" name="subtitle" type="text" autocomplete="off"
        class="form-control form-control-lg @error('subtitle') is-invalid @enderror"
        value="{{ $o('subtitle') }}" placeholder="e.g. Welcome to our clinic">
      @error('subtitle')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label class="form-label required" for="title">Headline</label>
      <textarea id="title" name="title" rows="4" required
        class="form-control form-control-lg @error('title') is-invalid @enderror"
        placeholder="Your main message&#10;Second line if you like">{{ $o('title') }}</textarea>
      @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label class="form-label" for="title_span">Accent line <span class="text-secondary fw-normal">(optional)</span></label>
      <textarea id="title_span" name="title_span" rows="2"
        class="form-control @error('title_span') is-invalid @enderror"
        placeholder="Shows in the highlighted style next to or below the headline">{{ $o('title_span') }}</textarea>
      @error('title_span')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-0">
      <label class="form-label" for="description">Supporting paragraph</label>
      <textarea id="description" name="description" rows="4"
        class="form-control @error('description') is-invalid @enderror"
        placeholder="A short sentence or two under the headline">{{ $o('description') }}</textarea>
      @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <h3 class="card-title mb-3">Button</h3>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="button_text">What the button says</label>
        <input id="button_text" name="button_text" type="text" autocomplete="off"
          class="form-control @error('button_text') is-invalid @enderror"
          value="{{ $o('button_text', 'More About Us') }}" placeholder="More About Us">
        @error('button_text')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-6">
        <label class="form-label" for="button_url">Where it goes</label>
        <input id="button_url" name="button_url" type="text" inputmode="url" autocomplete="off"
          class="form-control @error('button_url') is-invalid @enderror"
          value="{{ $o('button_url') }}" placeholder="/contact or a full https:// link">
        <div class="form-hint">Use a short path like <code>/contact</code> or paste any web address.</div>
        @error('button_url')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>
</div>

@php
  $defaultShowVideo = $slide ? ($slide->show_video ? '1' : '0') : '0';
  $defaultIsActive = $slide ? ($slide->is_active ? '1' : '0') : '1';
@endphp

<details class="card border-0 shadow-sm mb-3 slide-video-panel" @if (old('show_video', $defaultShowVideo) === '1' || $errors->has('video_url') || $errors->has('video_label')) open @endif>
  <summary class="card-body py-3 d-flex align-items-center justify-content-between user-select-none" style="cursor: pointer; list-style: none;">
    <span>
      <span class="fw-semibold">Watch video</span>
      <span class="text-secondary small ms-1">— optional block on the slide</span>
    </span>
    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-down text-secondary" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 9l6 6l6 -6" /></svg>
  </summary>
  <div class="card-body border-top pt-3">
    <input type="hidden" name="show_video" value="0">
    <label class="form-check form-switch mb-3">
      <input name="show_video" class="form-check-input" type="checkbox" value="1" data-slide-video-toggle
        @checked(old('show_video', $defaultShowVideo) === '1')>
      <span class="form-check-label">Show the “Watch video” area on this slide</span>
    </label>
    <div class="row g-3 slide-video-fields">
      <div class="col-md-8">
        <label class="form-label" for="video_url">Video link</label>
        <input id="video_url" name="video_url" type="text" inputmode="url" autocomplete="off"
          class="form-control @error('video_url') is-invalid @enderror"
          value="{{ $o('video_url') }}" placeholder="Paste a YouTube or other video URL">
        @error('video_url')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4">
        <label class="form-label" for="video_label">Label under the play icon</label>
        <input id="video_label" name="video_label" type="text" autocomplete="off"
          class="form-control @error('video_label') is-invalid @enderror"
          value="{{ $o('video_label', 'Watch Video') }}" placeholder="Watch Video">
        @error('video_label')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>
</details>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
      <div>
        <h3 class="card-title mb-0">Visibility</h3>
        <p class="text-secondary small mb-0 mt-1">Turn off to keep a draft without showing it on the homepage.</p>
      </div>
      <input type="hidden" name="is_active" value="0">
      <label class="form-check form-switch m-0">
        <input name="is_active" class="form-check-input" type="checkbox" value="1"
          @checked(old('is_active', $defaultIsActive) === '1')>
        <span class="form-check-label"><span data-active-label>Show on homepage</span></span>
      </label>
    </div>
  </div>
</div>

@push('styles')
  <style>
    .slide-video-panel summary::-webkit-details-marker { display: none; }
    .slide-video-panel summary .icon-tabler-chevron-down { transition: transform .2s ease; }
    .slide-video-panel[open] summary .icon-tabler-chevron-down { transform: rotate(180deg); }
    .slide-image-dropzone { transition: border-color .15s ease, background .15s ease; }
    .slide-image-dropzone.is-dragover { border-color: var(--tblr-primary) !important; background: var(--tblr-primary-lt); }
    .slide-live-preview { font-family: var(--tblr-font-sans-serif); }
    .slide-live-preview__title { font-size: 1.35rem; font-weight: 700; line-height: 1.25; white-space: pre-line; margin: 0; }
    .slide-live-preview__title span { display: block; color: var(--tblr-primary); font-weight: 700; margin-top: .25rem; white-space: pre-line; }
    .slide-live-preview__sub { font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; color: var(--tblr-secondary); margin: 0 0 .35rem; }
    .slide-live-preview__text { font-size: .8rem; color: var(--tblr-secondary); white-space: pre-line; margin: .5rem 0 0; line-height: 1.45; }
  </style>
@endpush

@push('scripts')
  <script>
    (function () {
      const dz = document.querySelector('[data-slide-dropzone]');
      const input = document.getElementById('image');
      const preview = document.getElementById('slide-image-preview');
      if (dz && input && preview) {
        ;['dragenter', 'dragover'].forEach((ev) => dz.addEventListener(ev, (e) => { e.preventDefault(); dz.classList.add('is-dragover'); }));
        ;['dragleave', 'drop'].forEach((ev) => dz.addEventListener(ev, () => dz.classList.remove('is-dragover')));
        dz.addEventListener('drop', (e) => {
          e.preventDefault();
          const f = e.dataTransfer.files && e.dataTransfer.files[0];
          if (f && f.type.startsWith('image/')) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });
        input.addEventListener('change', function () {
          const f = this.files && this.files[0];
          if (!f || !f.type.startsWith('image/')) return;
          preview.style.backgroundImage = 'url(' + URL.createObjectURL(f) + ')';
        });
      }

      const sub = document.getElementById('subtitle');
      const title = document.getElementById('title');
      const span = document.getElementById('title_span');
      const desc = document.getElementById('description');
      const pvSub = document.querySelector('[data-preview-subtitle]');
      const pvTitle = document.querySelector('[data-preview-title]');
      const pvAccentWrap = document.querySelector('[data-preview-accent-wrap]');
      const pvSpan = document.querySelector('[data-preview-span]');
      const pvDesc = document.querySelector('[data-preview-description]');
      function sync() {
        if (pvSub) { pvSub.textContent = (sub && sub.value.trim()) ? sub.value.trim() : '—'; pvSub.classList.toggle('opacity-50', !(sub && sub.value.trim())); }
        if (pvTitle) pvTitle.textContent = (title && title.value.trim()) ? title.value.trim() : 'Your headline will appear here';
        if (pvAccentWrap && pvSpan) {
          const t = span && span.value.trim();
          pvSpan.textContent = t || '';
          pvAccentWrap.style.display = t ? 'block' : 'none';
        }
        if (pvDesc) {
          const t = desc && desc.value.trim();
          pvDesc.textContent = t || '';
          pvDesc.style.display = t ? 'block' : 'none';
        }
      }
      [sub, title, span, desc].forEach((el) => el && el.addEventListener('input', sync));
      sync();

      const videoToggle = document.querySelector('[data-slide-video-toggle]');
      const videoFields = document.querySelector('.slide-video-fields');
      function syncVideo() {
        if (!videoFields) return;
        const on = videoToggle && videoToggle.checked;
        videoFields.classList.toggle('opacity-50', !on);
      }
      if (videoToggle) {
        videoToggle.addEventListener('change', syncVideo);
        syncVideo();
      }

      const activeInput = document.querySelector('.form-check-input[name="is_active"]');
      const activeLabel = document.querySelector('[data-active-label]');
      function syncActive() {
        if (activeLabel && activeInput) {
          activeLabel.textContent = activeInput.checked ? 'Show on homepage' : 'Hidden (draft)';
        }
      }
      if (activeInput) {
        activeInput.addEventListener('change', syncActive);
        syncActive();
      }
    })();
  </script>
@endpush
