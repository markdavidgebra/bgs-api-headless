<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5; margin: 0; padding: 24px; background: #f8fafc;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 24px;">
    <p style="margin-top: 0;">Hello{{ filled($recipientName) ? ' '.e($recipientName) : '' }},</p>

    <h2 style="margin: 0 0 8px;">{{ $promotion->name }}</h2>

    @if (filled($promotion->discount_label))
      <p style="margin: 0 0 12px; font-weight: 700; color: #7c3aed;">{{ $promotion->discount_label }} OFF</p>
    @endif

    @if (filled($customMessage))
      <p style="margin: 0 0 12px; white-space: pre-line;">{{ $customMessage }}</p>
    @elseif (filled($promotion->description))
      <p style="margin: 0 0 12px;">{{ $promotion->description }}</p>
    @endif

    <ul style="padding-left: 20px; margin: 0 0 16px;">
      @if ($promotion->code)
        <li><strong>Promo code:</strong> {{ $promotion->code }}</li>
      @endif
      @if ($promotion->start_date || $promotion->end_date)
        <li>
          <strong>Validity:</strong>
          {{ $promotion->start_date?->format('M j, Y') ?? 'Now' }}
          -
          {{ $promotion->end_date?->format('M j, Y') ?? 'Until further notice' }}
        </li>
      @endif
      @if (filled($promotion->display_note))
        <li><strong>Note:</strong> {{ $promotion->display_note }}</li>
      @endif
    </ul>

    <p style="margin-bottom: 0;">Book your next appointment to enjoy this offer.</p>
  </div>
</body>
</html>
