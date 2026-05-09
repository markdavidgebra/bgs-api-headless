@php
  $name = $name ?? 'icon_class';
  $selected = (string) old($name, $selected ?? '');
@endphp
<div class="mb-3 service-icon-dropdown-wrap">
  <label class="form-label" for="{{ $name }}_toggle">{{ __('Listing icon') }}</label>
  <input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ $selected }}"
    class="service-icon-dropdown-value">
  <div class="dropdown w-100">
    <button
      class="service-icon-dropdown-toggle dropdown-toggle @error($name) is-invalid @enderror"
      type="button"
      id="{{ $name }}_toggle"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      aria-haspopup="listbox"
      aria-label="{{ __('Choose icon') }}">
      <span class="service-icon-dropdown-preview">
        <i data-service-icon-current class="{{ \App\Models\Service::iconPreviewClassForPicker($selected) }}"
          aria-hidden="true"></i>
      </span>
      <span class="service-icon-dropdown-chevron" aria-hidden="true"></span>
    </button>
    <div class="dropdown-menu service-icon-dropdown-panel w-100">
      <input type="search" class="form-control form-control-sm service-icon-dropdown-filter"
        autocomplete="off" spellcheck="false" placeholder="{{ __('Search icons…') }}"
        aria-label="{{ __('Filter icons') }}">
      <div class="service-icon-dropdown-scroll" role="listbox" aria-label="{{ __('Service card icons') }}">
        @foreach ($options as $value => $label)
          @php
            $preview = \App\Models\Service::iconPreviewClassForPicker($value);
            $isSelected = $selected === (string) $value;
            $searchHaystack = \Illuminate\Support\Str::lower(trim($value.' '.$label));
          @endphp
          <button type="button"
            class="service-icon-dropdown-item @if ($isSelected) is-active @endif"
            data-value="{{ $value }}"
            data-search="{{ $searchHaystack }}"
            role="option"
            aria-selected="{{ $isSelected ? 'true' : 'false' }}"
            title="{{ $label }}"
            aria-label="{{ $label }}">
            <span class="service-icon-dropdown-item-icon"><i class="{{ $preview }}" aria-hidden="true"></i></span>
            <span class="visually-hidden">{{ $label }}</span>
          </button>
        @endforeach
      </div>
    </div>
  </div>
  <div class="form-hint">{{ __('Large icon set from Font Awesome — use search in the picker. Sync = default rotation on the home page.') }}</div>
  @error($name)
    <div class="invalid-feedback d-block">{{ $message }}</div>
  @enderror
</div>

@once
  @push('styles')
    <style>
      .service-icon-dropdown-toggle {
        --sid-ease: cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        min-height: 3.125rem;
        padding: 0.5rem 2.75rem 0.5rem 0.65rem;
        text-align: left;
        background-color: var(--tblr-bg-forms, #fff);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 0.375rem);
        box-shadow: 0 1px 2px rgba(24, 36, 51, 0.04);
        transition: border-color 0.2s var(--sid-ease), box-shadow 0.2s var(--sid-ease), background-color 0.2s var(--sid-ease);
      }

      .service-icon-dropdown-toggle:hover {
        border-color: color-mix(in srgb, var(--tblr-primary, #206bc4) 28%, var(--tblr-border-color, #dadfe5));
        box-shadow: 0 2px 12px rgba(24, 36, 51, 0.08);
      }

      .service-icon-dropdown-toggle:focus,
      .service-icon-dropdown-toggle:focus-visible {
        border-color: var(--tblr-primary, #206bc4);
        outline: 0;
        box-shadow: 0 0 0 3px rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.22);
      }

      .service-icon-dropdown-toggle::after {
        display: none;
      }

      .service-icon-dropdown-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 9999px;
        font-size: 1.2rem;
        line-height: 1;
        color: var(--tblr-primary, #206bc4);
        background: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.1);
        transition: transform 0.2s var(--sid-ease), background-color 0.2s var(--sid-ease);
      }

      .service-icon-dropdown-toggle:hover .service-icon-dropdown-preview {
        background: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.14);
      }

      .service-icon-dropdown-toggle[aria-expanded="true"] .service-icon-dropdown-preview {
        transform: scale(1.04);
        background: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.16);
      }

      .service-icon-dropdown-chevron {
        position: absolute;
        right: 0.85rem;
        top: 50%;
        width: 1.25rem;
        height: 1.25rem;
        margin-top: -0.625rem;
        opacity: 0.45;
        background-color: currentColor;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='black'%3E%3Cpath d='M8 11.25l-5.03-5.03a.75.75 0 01.02-1.08l.08-.07a.75.75 0 011.08.07L8 9.16l3.85-4.12a.75.75 0 011.13.99l-.07.08-5.5 5.5a.75.75 0 01-.99 0z'/%3E%3C/svg%3E") center / contain no-repeat;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='black'%3E%3Cpath d='M8 11.25l-5.03-5.03a.75.75 0 01.02-1.08l.08-.07a.75.75 0 011.08.07L8 9.16l3.85-4.12a.75.75 0 011.13.99l-.07.08-5.5 5.5a.75.75 0 01-.99 0z'/%3E%3C/svg%3E") center / contain no-repeat;
        transition: transform 0.25s var(--sid-ease), opacity 0.2s;
        pointer-events: none;
      }

      .service-icon-dropdown-toggle[aria-expanded="true"] .service-icon-dropdown-chevron {
        transform: rotate(180deg);
        opacity: 0.65;
      }

      .service-icon-dropdown-toggle {
        position: relative;
      }

      .service-icon-dropdown-panel {
        margin-top: 0.375rem !important;
        padding: 0.65rem;
        background: var(--tblr-bg-forms, #fff);
        border: 1px solid var(--tblr-border-color-translucent, rgba(4, 32, 69, 0.1));
        border-radius: calc(var(--tblr-border-radius, 0.375rem) + 2px);
        box-shadow:
          0 4px 24px -4px rgba(24, 36, 51, 0.12),
          0 2px 8px -2px rgba(24, 36, 51, 0.06);
      }

      .service-icon-dropdown-filter {
        margin-bottom: 0.5rem;
      }

      .service-icon-dropdown-item.is-filtered-out {
        display: none !important;
      }

      .service-icon-dropdown-scroll {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(2.85rem, 1fr));
        gap: 0.35rem;
        max-height: min(22rem, 55vh);
        overflow-y: auto;
        padding: 0.15rem;
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.35) transparent;
      }

      .service-icon-dropdown-scroll::-webkit-scrollbar {
        width: 6px;
      }

      .service-icon-dropdown-scroll::-webkit-scrollbar-thumb {
        background: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.28);
        border-radius: 99px;
      }

      .service-icon-dropdown-item {
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1;
        margin: 0;
        padding: 0;
        border: 1px solid transparent;
        border-radius: calc(var(--tblr-border-radius, 0.375rem) + 1px);
        background: rgba(24, 36, 51, 0.045);
        color: var(--tblr-body-color);
        font-size: 1.1rem;
        line-height: 1;
        cursor: pointer;
        transition:
          transform 0.16s var(--sid-ease),
          box-shadow 0.16s var(--sid-ease),
          border-color 0.16s var(--sid-ease),
          background-color 0.16s var(--sid-ease),
          color 0.16s var(--sid-ease);
      }

      .service-icon-dropdown-item:hover {
        background: var(--tblr-bg-forms, #fff);
        border-color: color-mix(in srgb, var(--tblr-primary, #206bc4) 22%, var(--tblr-border-color, #dadfe5));
        box-shadow: 0 2px 10px rgba(24, 36, 51, 0.08);
        transform: translateY(-1px);
        color: var(--tblr-primary, #206bc4);
      }

      .service-icon-dropdown-item:focus-visible {
        outline: 0;
        border-color: var(--tblr-primary, #206bc4);
        box-shadow: 0 0 0 2px rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.28);
      }

      .service-icon-dropdown-item.is-active {
        background: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.12);
        border-color: color-mix(in srgb, var(--tblr-primary, #206bc4) 45%, transparent);
        color: var(--tblr-primary, #206bc4);
        box-shadow: inset 0 0 0 1px rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.22);
      }

      .service-icon-dropdown-item.is-active:hover {
        transform: translateY(-1px);
      }

      .service-icon-dropdown-toggle.is-invalid {
        border-color: var(--tblr-danger, #d63939);
      }

      .service-icon-dropdown-toggle.is-invalid:focus,
      .service-icon-dropdown-toggle.is-invalid:focus-visible {
        box-shadow: 0 0 0 3px rgba(var(--tblr-danger-rgb, 214, 57, 57), 0.2);
      }
    </style>
  @endpush
@endonce

@once
  @push('scripts')
    <script>
      (function() {
        function syncToggleExpanded(toggle, open) {
          if (!toggle) {
            return;
          }
          toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function onIconPick(e) {
          var item = e.target.closest('.service-icon-dropdown-item');
          if (!item) {
            return;
          }
          e.preventDefault();
          var wrap = item.closest('.service-icon-dropdown-wrap');
          if (!wrap) {
            return;
          }
          var hidden = wrap.querySelector('input.service-icon-dropdown-value');
          if (!hidden) {
            return;
          }
          var toggle = wrap.querySelector('.service-icon-dropdown-toggle');
          var current = wrap.querySelector('[data-service-icon-current]');
          if (!toggle || !current) {
            return;
          }
          var value = item.getAttribute('data-value');
          if (value === null) {
            value = '';
          }
          hidden.value = value;
          var pickIcon = item.querySelector('.service-icon-dropdown-item-icon i');
          if (pickIcon) {
            current.className = pickIcon.className;
          }
          wrap.querySelectorAll('.service-icon-dropdown-item').forEach(function(btn) {
            var on = btn === item;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
          });
          if (window.bootstrap && toggle) {
            bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
          }
        }

        function setIconFilterQuery(wrap, query) {
          var q = (query || '').trim().toLowerCase();
          if (!wrap) {
            return;
          }
          wrap.querySelectorAll('.service-icon-dropdown-item').forEach(function(btn) {
            var hay = (btn.getAttribute('data-search') || '').toLowerCase();
            var hide = q !== '' && hay.indexOf(q) === -1;
            btn.classList.toggle('is-filtered-out', hide);
          });
        }

        function onFilterInput(e) {
          var input = e.target.closest('.service-icon-dropdown-filter');
          if (!input) {
            return;
          }
          var panel = input.closest('.service-icon-dropdown-panel');
          var wrap = input.closest('.service-icon-dropdown-wrap');
          if (!panel || !wrap) {
            return;
          }
          setIconFilterQuery(wrap, input.value);
        }

        function onDropdownEvents() {
          document.querySelectorAll('.service-icon-dropdown-toggle').forEach(function(toggle) {
            toggle.addEventListener('shown.bs.dropdown', function() {
              syncToggleExpanded(toggle, true);
              var wrap = toggle.closest('.service-icon-dropdown-wrap');
              if (!wrap) {
                return;
              }
              var filter = wrap.querySelector('.service-icon-dropdown-filter');
              if (filter) {
                filter.value = '';
                setIconFilterQuery(wrap, '');
                filter.focus();
              }
            });
            toggle.addEventListener('hidden.bs.dropdown', function() {
              syncToggleExpanded(toggle, false);
            });
          });
        }

        function bind() {
          document.body.addEventListener('click', onIconPick);
          document.body.addEventListener('input', onFilterInput);
          onDropdownEvents();
        }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', bind);
        } else {
          bind();
        }
      })();
    </script>
  @endpush
@endonce
