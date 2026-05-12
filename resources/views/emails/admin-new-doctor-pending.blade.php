<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Doctor pending') }}</title>
</head>
<body style="font-family: Arial, sans-serif;">
  <p>{{ __('A new doctor was created and is pending approval before they can sign in.') }}</p>
  <p>
    <strong>{{ __('Name') }}:</strong> {{ $doctor->name }}<br>
    <strong>{{ __('Email') }}:</strong> {{ $doctor->email }}
  </p>
  <p><a href="{{ route('admin.doctors.show', $doctor->id) }}">{{ __('Review doctor') }}</a></p>
</body>
</html>
