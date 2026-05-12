<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('New registration') }}</title>
</head>
<body style="font-family: Arial, sans-serif;">
  <p>{{ __('A new patient registered and is awaiting approval.') }}</p>
  <p>
    <strong>{{ __('Name') }}:</strong> {{ $patient->name }}<br>
    <strong>{{ __('Email') }}:</strong> {{ $patient->email }}
  </p>
  <p><a href="{{ route('admin.registrations') }}">{{ __('Open registrations') }}</a></p>
</body>
</html>
