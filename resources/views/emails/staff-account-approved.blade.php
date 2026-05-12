<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Account Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  <p>Hello {{ $staff->name }},</p>

  <p>Your staff account has been approved. You can now access the admin panel.</p>

  <p>
    <strong>Username (Email):</strong> {{ $staff->email }}<br>
    @if (! empty($plainPassword))
      <strong>Password:</strong> {{ $plainPassword }}<br>
    @endif
    <strong>Login URL:</strong> <a href="{{ route('admin.login') }}">{{ route('admin.login') }}</a>
  </p>

  <p>For security, please change your password after your first login.</p>

  <p>Thank you.</p>
</body>
</html>
