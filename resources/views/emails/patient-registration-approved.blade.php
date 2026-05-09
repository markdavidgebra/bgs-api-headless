<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  <p>Hello {{ $name }},</p>

  <p>Your account registration has been approved by the admin. You can now log in using the credentials below:</p>

  <p>
    <strong>Username (Email):</strong> {{ $emailAddress }}<br>
    <strong>Password:</strong> {{ $plainPassword }}
  </p>

  <p>For security, please log in and change your password as soon as possible.</p>

  <p>Thank you.</p>
</body>
</html>
