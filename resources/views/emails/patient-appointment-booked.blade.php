<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
  <p>Hello {{ $appointment->patient?->name ?? 'Patient' }},</p>

  <p>Your appointment request has been successfully submitted. Here are your booking details:</p>

  <p>
    <strong>Appointment No:</strong> {{ $appointment->appointment_no }}<br>
    <strong>Date:</strong> {{ $appointment->appointment_date?->format('M j, Y') ?? '—' }}<br>
    <strong>Time:</strong> {{ $appointment->time_display }}<br>
    <strong>Service:</strong> {{ $appointment->service?->name ?? '—' }}<br>
    <strong>Doctor:</strong> {{ $appointment->doctor?->name ?? '—' }}<br>
    <strong>Status:</strong> {{ ucfirst((string) $appointment->status) }}
  </p>

  <p>We will notify you for any updates regarding this booking.</p>

  <p>Thank you.</p>
</body>
</html>
