<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Approval queue') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
  <p>{{ __('Summary of items that may need your attention:') }}</p>
  <ul>
    <li>{{ __('Pending patient registrations') }}: <strong>{{ $pendingPatients }}</strong></li>
    <li>{{ __('Pending doctor accounts') }}: <strong>{{ $pendingDoctors }}</strong></li>
    <li>{{ __('Draft staff accounts (awaiting approval)') }}: <strong>{{ $draftStaff }}</strong></li>
  </ul>
  @if ($pendingPatients > 0)
    <p><a href="{{ route('admin.registrations') }}">{{ __('Review patient registrations') }}</a></p>
  @endif
  @if ($pendingDoctors > 0)
    <p><a href="{{ route('admin.doctors') }}?status=pending">{{ __('Review doctors') }}</a></p>
  @endif
  @if ($draftStaff > 0)
    <p><a href="{{ route('admin.staffs') }}">{{ __('Review staff') }}</a></p>
  @endif
</body>
</html>
