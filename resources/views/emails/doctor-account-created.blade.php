<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  <p>Hello Dr. {{ $doctor->name }},</p>

  <p>Your account has been created. You can now log in using the credentials below:</p>

  <p>
    <strong>Username (Email):</strong> {{ $doctor->email }}<br>
    <strong>Password:</strong> {{ $plainPassword }}
  </p>

  <p>
    <strong>Login URL:</strong>
    <a href="{{ route('login') }}">{{ route('login') }}</a>
  </p>

  <p>For security, please change your password after your first login.</p>

  <p>Thank you.</p>
</body>
</html>
