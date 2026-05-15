@once
  <script>
    window.blockSundayDateInput = function (input) {
      if (!input) return;
      var message = @json(\App\Support\AppointmentBookingRules::closedDateMessage());
      function validateSunday() {
        if (!input.value) {
          input.setCustomValidity('');
          return;
        }
        var parts = input.value.split('-');
        if (parts.length !== 3) {
          input.setCustomValidity('');
          return;
        }
        var date = new Date(
          parseInt(parts[0], 10),
          parseInt(parts[1], 10) - 1,
          parseInt(parts[2], 10)
        );
        if (date.getDay() === 0) {
          input.setCustomValidity(message);
          input.reportValidity();
        } else {
          input.setCustomValidity('');
        }
      }
      input.addEventListener('change', validateSunday);
      input.addEventListener('input', validateSunday);
    };
  </script>
@endonce
