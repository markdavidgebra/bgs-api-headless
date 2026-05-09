@php
  $defaultHref = $defaultHref ?? asset('favicon.ico');
  $defaultType = $defaultType ?? null;
  $variant = $variant ?? 'simple';

  $raw = \App\Models\AppSetting::getValue('site_favicon');
  $href = null;
  $ext = '';

  if ($raw) {
    if (\Illuminate\Support\Str::startsWith($raw, ['http://', 'https://'])) {
      $href = $raw;
      $ext = strtolower((string) pathinfo((string) parse_url($raw, PHP_URL_PATH), PATHINFO_EXTENSION));
    } else {
      $normalized = ltrim($raw, '/');
      if (is_file(public_path($normalized))) {
        $href = asset($normalized);
        $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
      }
    }
  }

  $mime = match ($ext) {
    'svg' => 'image/svg+xml',
    'png' => 'image/png',
    'ico' => 'image/x-icon',
    'jpg', 'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    default => null,
  };
@endphp
@if ($href)
  <link rel="icon" href="{{ $href }}"@if ($mime) type="{{ $mime }}"@endif>
  @if ($variant === 'frontend')
    <link rel="apple-touch-icon" href="{{ $href }}">
  @endif
@elseif ($variant === 'frontend')
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/assets/images/favicons/apple-touch-icon.png') }}" />
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/assets/images/favicons/favicon-32x32.png') }}" />
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('frontend/assets/images/favicons/favicon-16x16.png') }}" />
  <link rel="manifest" href="{{ asset('frontend/assets/images/favicons/site.webmanifest') }}" />
@else
  <link rel="icon" href="{{ $defaultHref }}"@if ($defaultType) type="{{ $defaultType }}"@endif>
@endif
