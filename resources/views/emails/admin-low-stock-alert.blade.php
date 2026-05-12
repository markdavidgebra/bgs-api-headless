<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Low stock alert') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
  <p>{{ __('The following products need attention (low or out of stock):') }}</p>
  <ul>
    @foreach ($products as $p)
      <li>
        <strong>{{ $p->name }}</strong>
        — {{ __('Qty') }}: {{ (int) $p->stock_quantity }}
        ({{ __('min') }} {{ (int) $p->minimum_stock_alert }})
        — <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $p->stock_status) }}</span>
      </li>
    @endforeach
  </ul>
  <p><a href="{{ route('admin.products.inventory') }}">{{ __('Open inventory in admin') }}</a></p>
</body>
</html>
