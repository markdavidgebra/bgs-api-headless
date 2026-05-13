<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  @php
    $nameParts = preg_split('/\s+/', trim((string) $doctor->name)) ?: [];
    $lastName = end($nameParts) ?: (string) $doctor->name;
    $portalLink = url('/login?tab=staff');
  @endphp

  <p>Dear Dr. {{ $lastName }},</p>

  <p>Greetings from BioGlow Solutions (BGS) Beauty and Wellness Hub.</p>

  <p>Welcome to the BGS Physician Portal. We are pleased to inform you that your account has been successfully created.</p>

  <p>Please see your login credentials below:</p>

  <p>
    <strong>Username:</strong> {{ $doctor->email }}<br>
    <strong>Temporary Password:</strong> {{ $plainPassword }}
  </p>

  <p>You may access the portal through the link below:</p>

  <p><a href="{{ $portalLink }}">{{ $portalLink }}</a></p>

  <p>Through the BGS portal, you will be able to conveniently access and manage:</p>

  <ul>
    <li>Patient appointments</li>
    <li>Patient records and information</li>
    <li>Programs and treatment plans</li>
    <li>Services and procedures</li>
    <li>Packages</li>
    <li>Other clinic-related updates and resources</li>
  </ul>

  <p>For security purposes, once you have successfully accessed your account, please update your temporary password within twenty-four (24) hours.</p>

  <p>We are excited to have you onboard as part of our growing commitment to health, wellness, and longevity-focused care.</p>

  <p>Should you need any assistance accessing your account, please feel free to contact us.</p>

  <p>Thank you, and welcome to BioGlow Solutions (BGS) Beauty and Wellness Hub.</p>

  <p>
    Warm regards,<br>
    BioGlow Solutions (BGS) Beauty and Wellness Hub
  </p>
</body>
</html>
