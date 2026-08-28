<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Clinical staff pending') }}</title>
</head>
<body style="font-family: Arial, sans-serif;">
  <p>{{ __('A new clinical staff member was created and is pending approval before they can sign in.') }}</p>
  <p>
    <strong>{{ __('Name') }}:</strong> {{ $doctor->name }}<br>
    <strong>{{ __('Email') }}:</strong> {{ $doctor->email }}
  </p>
  <p><a href="{{ route('admin.clinical-staff.show', $doctor->id) }}">{{ __('Review clinical staff') }}</a></p>
</body>
</html>
