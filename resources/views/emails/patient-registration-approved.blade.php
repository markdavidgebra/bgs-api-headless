<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  @php
    $nameParts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $firstName = $nameParts[0] ?? (string) $name;
    $loginUrl = config('app.patient_portal_url') ?: route('login');
    $guestBookingUrl = rtrim((string) config('app.website_url', 'https://bioglowsolutions.com'), '/').'/';
  @endphp

  <p>Hi {{ $firstName }},</p>

  <p>Greetings from BioGlow Solutions (BGS) Beauty and Wellness Hub.</p>

  <p>We are pleased to inform you that your patient account has been <strong>approved</strong>. You can now sign in to the patient portal using the credentials below:</p>

  <p>
    <strong>Email:</strong> {{ $emailAddress }}<br>
    @if (! empty($plainPassword))
      <strong>Password:</strong> {{ $plainPassword }}<br>
    @else
      <strong>Password:</strong> Use the password you chose when registering, or click “Forgot password” on the sign-in page if you need a reset link.<br>
    @endif
    <strong>Sign in:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
  </p>

  <p>Through the patient portal, you can view appointments, payments, subscriptions, and other account details.</p>

  <p>For your security, please change your password after your first sign-in.</p>

  <p>You may also book an appointment as a guest here:</p>
  <p><a href="{{ $guestBookingUrl }}">{{ $guestBookingUrl }}</a></p>

  <p>
    <strong>Clinic address:</strong><br>
    Bormaheco, Inc., Davao Park Building, Bajada St., J.P. Laurel Ave., Davao City
  </p>

  <p>
    For questions or assistance, contact us at 09566193919 or
    <a href="mailto:inquiry@bioglowsolutions.com">inquiry@bioglowsolutions.com</a>.
  </p>

  <p>Thank you for choosing BioGlow Solutions (BGS) Beauty and Wellness Hub. We look forward to serving you.</p>

  <p>
    Warm regards,<br>
    BioGlow Solutions (BGS) Beauty and Wellness Hub
  </p>
</body>
</html>
