@once
  <style>
      .bgs-bookable-date-input {
        cursor: pointer;
        background-color: var(--tblr-bg-forms, var(--bs-body-bg, #fff));
      }

      .bgs-date-picker {
        position: absolute;
        z-index: 1060;
        min-width: 18rem;
        padding: 0.75rem;
        background: var(--tblr-bg-surface, #fff);
        border: 1px solid var(--tblr-border-color, #dce1e7);
        border-radius: var(--tblr-border-radius, 4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      }

      .bgs-date-picker__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-weight: 600;
      }

      .bgs-date-picker__weekdays,
      .bgs-date-picker__days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.125rem;
        text-align: center;
      }

      .bgs-date-picker__weekdays {
        margin-bottom: 0.25rem;
        font-size: 0.75rem;
        color: var(--tblr-secondary, #6c757d);
      }

      .bgs-date-picker__day {
        border: 0;
        background: transparent;
        border-radius: var(--tblr-border-radius, 4px);
        padding: 0.35rem 0;
        font-size: 0.875rem;
        line-height: 1.2;
        color: var(--tblr-body-color, #1e293b);
      }

      .bgs-date-picker__day:not(:disabled):hover {
        background: var(--tblr-active-bg, rgba(32, 107, 196, 0.08));
        color: var(--tblr-primary, #206bc4);
      }

      .bgs-date-picker__day--muted {
        color: var(--tblr-secondary, #6c757d);
        opacity: 0.45;
      }

      .bgs-date-picker__day:disabled {
        color: var(--tblr-secondary, #6c757d);
        opacity: 0.35;
        cursor: not-allowed;
        pointer-events: none;
      }

      .bgs-date-picker__day--selected {
        background: var(--tblr-primary, #206bc4);
        color: #fff;
      }

      .bgs-date-picker__day--selected:disabled {
        background: transparent;
        color: var(--tblr-secondary, #6c757d);
      }
  </style>

  <script>
    window.getBlockSundayDateInputValue = function (input) {
      if (!input) {
        return '';
      }
      if (input._bgsBookableDateHidden) {
        return input._bgsBookableDateHidden.value;
      }
      return input.value;
    };

    window.blockSundayDateInput = function (input, options) {
      if (!input || input.dataset.bgsBookableDateInit === '1') {
        return;
      }

      options = options || {};
      var useLongDisplay = options.displayFormat === 'long';
      var config = {
        minDate: options.minDate || input.getAttribute('min') || null,
        onChange: typeof options.onChange === 'function' ? options.onChange : null,
        displayFormat: useLongDisplay ? 'long' : 'iso',
      };
      input._bgsBookableDateConfig = config;
      input.dataset.bgsBookableDateInit = '1';

      if (input.type === 'date') {
        input.type = 'text';
      }

      var isoValue = input.value || '';
      var hidden = null;

      if (useLongDisplay && input.name) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = input.name;
        hidden.value = isoValue;
        input.removeAttribute('name');
        input.parentNode.insertBefore(hidden, input.nextSibling);
        input._bgsBookableDateHidden = hidden;
      }

      input.setAttribute('readonly', 'readonly');
      input.setAttribute('autocomplete', 'off');
      input.classList.add('bgs-bookable-date-input');
      input.removeAttribute('min');

      var monthLabels = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.', 'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.'];

      var picker = document.createElement('div');
      picker.className = 'bgs-date-picker d-none';
      picker.setAttribute('role', 'dialog');
      picker.setAttribute('aria-label', 'Choose a date');
      document.body.appendChild(picker);

      var view = new Date();
      if (config.minDate) {
        var minParts = config.minDate.split('-');
        view = new Date(parseInt(minParts[0], 10), parseInt(minParts[1], 10) - 1, parseInt(minParts[2], 10));
      }
      view.setDate(1);

      function pad(n) {
        return String(n).padStart(2, '0');
      }

      function formatYmd(date) {
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
      }

      function parseYmd(value) {
        var parts = value.split('-');
        if (parts.length !== 3) {
          return null;
        }
        return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
      }

      function formatLong(ymd) {
        var date = parseYmd(ymd);
        if (!date || isNaN(date.getTime())) {
          return '';
        }
        return monthLabels[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
      }

      function getIsoValue() {
        if (hidden) {
          return hidden.value;
        }
        return input.value;
      }

      function setIsoValue(ymd) {
        if (hidden) {
          hidden.value = ymd || '';
          input.value = ymd ? formatLong(ymd) : '';
          return;
        }
        input.value = ymd || '';
      }

      function isSunday(date) {
        return date.getDay() === 0;
      }

      function isBeforeMin(date) {
        if (!config.minDate) {
          return false;
        }
        return formatYmd(date) < config.minDate;
      }

      function isDisabledDay(date) {
        return isSunday(date) || isBeforeMin(date);
      }

      function positionPicker() {
        var rect = input.getBoundingClientRect();
        picker.style.top = window.scrollY + rect.bottom + 4 + 'px';
        picker.style.left = window.scrollX + rect.left + 'px';
      }

      function hidePicker() {
        picker.classList.add('d-none');
      }

      function showPicker() {
        var currentIso = getIsoValue();
        if (currentIso) {
          var selected = parseYmd(currentIso);
          if (selected && !isNaN(selected.getTime())) {
            view = new Date(selected.getFullYear(), selected.getMonth(), 1);
          }
        }
        renderPicker();
        positionPicker();
        picker.classList.remove('d-none');
      }

      function renderPicker() {
        var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var year = view.getFullYear();
        var month = view.getMonth();
        var firstOfMonth = new Date(year, month, 1);
        var startOffset = firstOfMonth.getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var selectedValue = getIsoValue();
        var html = '';

        html += '<div class="bgs-date-picker__header">';
        html += '<button type="button" class="btn btn-sm btn-icon btn-ghost-secondary bgs-date-picker__prev" aria-label="Previous month">&lsaquo;</button>';
        html += '<span>' + monthNames[month] + ' ' + year + '</span>';
        html += '<button type="button" class="btn btn-sm btn-icon btn-ghost-secondary bgs-date-picker__next" aria-label="Next month">&rsaquo;</button>';
        html += '</div>';
        html += '<div class="bgs-date-picker__weekdays">';
        ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].forEach(function (label) {
          html += '<div>' + label + '</div>';
        });
        html += '</div><div class="bgs-date-picker__days">';

        for (var i = 0; i < startOffset; i++) {
          html += '<span></span>';
        }

        for (var day = 1; day <= daysInMonth; day++) {
          var date = new Date(year, month, day);
          var value = formatYmd(date);
          var disabled = isDisabledDay(date);
          var classes = ['bgs-date-picker__day'];
          if (disabled) {
            classes.push('bgs-date-picker__day--muted');
          }
          if (value === selectedValue) {
            classes.push('bgs-date-picker__day--selected');
          }
          html += '<button type="button" class="' + classes.join(' ') + '" data-date="' + value + '"'
            + (disabled ? ' disabled' : '')
            + '>' + day + '</button>';
        }

        html += '</div>';
        picker.innerHTML = html;

        picker.querySelector('.bgs-date-picker__prev').addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          view.setMonth(view.getMonth() - 1);
          renderPicker();
        });

        picker.querySelector('.bgs-date-picker__next').addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          view.setMonth(view.getMonth() + 1);
          renderPicker();
        });

        picker.querySelectorAll('.bgs-date-picker__day:not(:disabled)').forEach(function (button) {
          button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var picked = button.getAttribute('data-date');
            setIsoValue(picked);
            input.setCustomValidity('');
            hidePicker();
            var changeTarget = hidden || input;
            changeTarget.dispatchEvent(new Event('change', { bubbles: true }));
            if (config.onChange) {
              config.onChange(picked);
            }
          });
        });
      }

      input.addEventListener('mousedown', function (event) {
        event.preventDefault();
      });

      input.addEventListener('click', function () {
        showPicker();
      });

      input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          showPicker();
        }
        if (event.key === 'Escape') {
          hidePicker();
        }
      });

      document.addEventListener('click', function (event) {
        if (!picker.contains(event.target) && event.target !== input) {
          hidePicker();
        }
      });

      window.addEventListener('resize', hidePicker);
      window.addEventListener('scroll', hidePicker, true);

      function validateValue() {
        var iso = getIsoValue();
        if (!iso) {
          input.setCustomValidity('');
          return;
        }
        var date = parseYmd(iso);
        if (!date || isDisabledDay(date)) {
          input.setCustomValidity(@json(\App\Support\AppointmentBookingRules::closedDateMessage()));
          input.reportValidity();
          setIsoValue('');
        } else {
          input.setCustomValidity('');
        }
      }

      if (isoValue) {
        setIsoValue(isoValue);
      }

      (hidden || input).addEventListener('change', validateValue);
    };

    window.setBlockSundayDateInputMin = function (input, minDate) {
      if (!input || !input._bgsBookableDateConfig) {
        return;
      }
      input._bgsBookableDateConfig.minDate = minDate || null;
      var current = window.getBlockSundayDateInputValue(input);
      if (current && minDate && current < minDate) {
        if (input._bgsBookableDateHidden) {
          input._bgsBookableDateHidden.value = '';
          input.value = '';
        } else {
          input.value = '';
        }
        (input._bgsBookableDateHidden || input).dispatchEvent(new Event('change', { bubbles: true }));
      }
    };
  </script>
@endonce
