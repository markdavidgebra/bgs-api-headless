<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Staff draft') }}</title>
</head>
<body style="font-family: Arial, sans-serif;">
  <p>{{ __('A new staff account was created as draft and needs approval before login is enabled.') }}</p>
  <p>
    <strong>{{ __('Name') }}:</strong> {{ $staff->name }}<br>
    <strong>{{ __('Email') }}:</strong> {{ $staff->email }}<br>
    <strong>{{ __('Role') }}:</strong> {{ $staff->role }}
  </p>
  <p><a href="{{ route('admin.staffs.show', $staff->id) }}">{{ __('Review staff') }}</a></p>
</body>
</html>
