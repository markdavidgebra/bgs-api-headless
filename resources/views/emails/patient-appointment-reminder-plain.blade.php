<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('Appointment reminder') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  <p>{{ __('Hello :name,', ['name' => $appointment->patient?->name ?? __('Patient')]) }}</p>

  <p>{{ __('This is a reminder that you have an appointment scheduled for tomorrow.') }}</p>

  <p>
    <strong>{{ __('Appointment No') }}:</strong> {{ $appointment->appointment_no ?? '#'.$appointment->id }}<br>
    <strong>{{ __('Date') }}:</strong> {{ $appointment->appointment_date?->format('M j, Y') ?? '—' }}<br>
    <strong>{{ __('Time') }}:</strong> {{ $appointment->time_display }}<br>
    <strong>{{ __('Service') }}:</strong> {{ $appointment->service?->name ?? '—' }}<br>
    <strong>{{ __('Clinical staff') }}:</strong> {{ $appointment->doctor?->name ?? '—' }}<br>
    <strong>{{ __('Status') }}:</strong> {{ ucfirst((string) $appointment->status) }}
  </p>

  <p><a href="{{ route('patient.appointments.show', $appointment) }}">{{ __('View appointment in portal') }}</a></p>

  <p>{{ __('Thank you.') }}</p>
</body>
</html>
