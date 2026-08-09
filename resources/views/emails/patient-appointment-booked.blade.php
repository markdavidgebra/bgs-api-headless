<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title>Appointment Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f6eef3; -webkit-text-size-adjust:100%;">
  @php
    $statusRaw = strtolower((string) ($appointment->status ?? 'pending'));
    $statusColors = [
        'confirmed' => ['bg' => '#e3f5e8', 'text' => '#1e7a3d'],
        'completed' => ['bg' => '#e3f5e8', 'text' => '#1e7a3d'],
        'pending' => ['bg' => '#fdf1de', 'text' => '#9a6b12'],
        'cancelled' => ['bg' => '#fbe7e9', 'text' => '#b3261e'],
        'rescheduled' => ['bg' => '#e7effd', 'text' => '#2554a6'],
    ];
    $statusStyle = $statusColors[$statusRaw] ?? $statusColors['pending'];

    $timeRaw = $appointment->time_display;
    $timeLabel = $timeRaw && $timeRaw !== '—'
        ? \Illuminate\Support\Carbon::createFromFormat('H:i', $timeRaw)->format('g:i A')
        : '—';
    $dateLabel = $appointment->appointment_date?->format('l, F j, Y') ?? '—';
    $firstName = trim(explode(' ', trim((string) ($appointment->patient?->name ?? '')))[0] ?? '') ?: 'there';
  @endphp

  <div style="padding: 32px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; margin: 0 auto; background:#ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #efdfe9;">
      <tr>
        <td style="background-color:#a8577c; background-image: linear-gradient(135deg, #c7819d 0%, #a8577c 100%); padding: 36px 40px; text-align: center;">
          <div style="font-size: 20px; font-weight: 700; letter-spacing: 0.04em; color:#ffffff;">BIOGLOW SOLUTIONS</div>
          <div style="margin-top: 6px; font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.85);">Beauty &amp; Wellness Hub</div>
        </td>
      </tr>

      <tr>
        <td style="padding: 36px 40px 4px;">
          <span style="display:inline-block; padding: 6px 14px; border-radius: 999px; background-color:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['text'] }}; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
            {{ $appointment->status_label ?? ucfirst($statusRaw) }}
          </span>
          <h1 style="margin: 18px 0 6px; font-size: 22px; line-height: 1.3; color:#2f232c;">Your appointment is booked!</h1>
          <p style="margin:0; font-size: 14px; color:#6b5b66; line-height:1.6;">
            Hi {{ $firstName }}, thank you for booking with us. Here's a summary of your upcoming visit.
          </p>
        </td>
      </tr>

      <tr>
        <td style="padding: 20px 40px 8px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#faf5f8; border: 1px solid #efdfe9; border-radius: 12px;">
            <tr>
              <td style="padding: 6px 22px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="padding: 12px 0; font-size: 13px; color:#8a7a85;">Appointment No.</td>
                    <td style="padding: 12px 0; font-size: 14px; font-weight: 600; color:#2f232c; text-align:right;">{{ $appointment->appointment_no }}</td>
                  </tr>
                  <tr><td colspan="2" style="border-top: 1px solid #efdfe9; line-height:0;">&nbsp;</td></tr>
                  <tr>
                    <td style="padding: 12px 0; font-size: 13px; color:#8a7a85;">Date</td>
                    <td style="padding: 12px 0; font-size: 14px; font-weight: 600; color:#2f232c; text-align:right;">{{ $dateLabel }}</td>
                  </tr>
                  <tr><td colspan="2" style="border-top: 1px solid #efdfe9; line-height:0;">&nbsp;</td></tr>
                  <tr>
                    <td style="padding: 12px 0; font-size: 13px; color:#8a7a85;">Time</td>
                    <td style="padding: 12px 0; font-size: 14px; font-weight: 600; color:#2f232c; text-align:right;">{{ $timeLabel }}</td>
                  </tr>
                  <tr><td colspan="2" style="border-top: 1px solid #efdfe9; line-height:0;">&nbsp;</td></tr>
                  <tr>
                    <td style="padding: 12px 0; font-size: 13px; color:#8a7a85;">Service</td>
                    <td style="padding: 12px 0; font-size: 14px; font-weight: 600; color:#2f232c; text-align:right;">{{ $appointment->service?->name ?? '—' }}</td>
                  </tr>
                  <tr><td colspan="2" style="border-top: 1px solid #efdfe9; line-height:0;">&nbsp;</td></tr>
                  <tr>
                    <td style="padding: 12px 0; font-size: 13px; color:#8a7a85;">Doctor</td>
                    <td style="padding: 12px 0; font-size: 14px; font-weight: 600; color:#2f232c; text-align:right;">{{ $appointment->doctor?->name ?? '—' }}</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      @if (! empty($actionUrl))
        <tr>
          <td style="padding: 28px 40px 4px; text-align:center;">
            <a href="{{ $actionUrl }}" style="display:inline-block; background-color:#c7819d; color:#ffffff; text-decoration:none; font-size: 14px; font-weight: 600; padding: 13px 30px; border-radius: 999px;">
              View appointment
            </a>
          </td>
        </tr>
      @endif

      <tr>
        <td style="padding: 24px 40px 32px;">
          <p style="margin:0; font-size: 13px; color:#8a7a85; line-height:1.6; text-align:center;">
            We will notify you here by email if there are any updates regarding this booking.
          </p>
        </td>
      </tr>

      <tr>
        <td style="padding: 22px 40px; background-color:#faf5f8; border-top: 1px solid #efdfe9;">
          <p style="margin:0 0 6px; font-size: 12px; color:#8a7a85; text-align:center; line-height:1.5;">
            Bormaheco, Inc., Davao Park Building, Bajada St., J.P. Laurel Ave., Davao City
          </p>
          <p style="margin:0; font-size: 12px; color:#8a7a85; text-align:center;">
            09566193919 &middot; <a href="mailto:inquiry@bioglowsolutions.com" style="color:#a8577c; text-decoration:none;">inquiry@bioglowsolutions.com</a>
          </p>
        </td>
      </tr>
    </table>

    <p style="max-width: 560px; margin: 18px auto 0; text-align:center; font-size: 11px; color:#b3a3ac; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
      &copy; {{ date('Y') }} BioGlow Solutions (BGS) Beauty and Wellness Hub. All rights reserved.
    </p>
  </div>
</body>
</html>
