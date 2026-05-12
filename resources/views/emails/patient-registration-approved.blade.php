<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  @php
    $nameParts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $firstName = $nameParts[0] ?? (string) $name;
    $guestBookingLink = route('appointment');
  @endphp

  <p>Hi Patient {{ $firstName }},</p>

  <p>Thank you for your interest in BioGlow Solutions (BGS) Beauty and Wellness Hub.</p>

  <p>We have successfully received your request to create an account with us. To ensure the security and authenticity of patient records, account approvals are completed upon your first visit and availment of services at our clinic.</p>

  <p>In the meantime, you may still conveniently book an appointment with us as a guest by clicking the link below:</p>
  <p><a href="{{ $guestBookingLink }}">{{ $guestBookingLink }}</a></p>

  <p>We look forward to welcoming you to our clinic and supporting you in your health, wellness, and longevity journey.</p>

  <p>
    <strong>Clinic Address:</strong><br>
    Bormaheco, Inc., Davao Park Building, Bajada St., J.P. Laurel Ave., Davao City
  </p>

  <p>If you have already booked your appointment as a guest, we look forward to seeing you at our clinic soon.</p>

  <p>
    For any questions or assistance, feel free to reach out to us at:<br>
    09566193919<br>
    or email us at <a href="mailto:inquiry@bioglowsolutions.com">inquiry@bioglowsolutions.com</a>
  </p>

  <p>Thank you for choosing BioGlow Solutions (BGS) Beauty and Wellness Hub. We are excited to serve you.</p>

  <p>
    Warm regards,<br>
    BioGlow Solutions (BGS) Beauty and Wellness Hub
  </p>
</body>
</html>
