<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  <p>Hello {{ $staff->name }},</p>

  <p>Your staff account has been created successfully.</p>

  <p>
    <strong>Username:</strong> {{ $staff->email }}<br>
    <strong>Temporary Password:</strong> {{ $temporaryPassword }}
  </p>

  <p>
    Your account is currently marked as <strong>Draft</strong> and cannot log in yet.
    You will receive another email once an admin approves your account.
  </p>

  <p>Thank you.</p>
</body>
</html>
