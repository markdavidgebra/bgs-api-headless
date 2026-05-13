<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  @include('partials.site-favicon')
  <title>Dashboard</title>
  <!-- CSS files -->
  <link href="{{asset('admin/assets/dist/css/tabler.min.css?1692870487')}}" rel="stylesheet" />
  <link href="{{asset('admin/assets/dist/css/demo.min.css?1692870487')}}" rel="stylesheet" />
  <link href="{{ asset('frontend/assets/css/font-awesome-all.css') }}" rel="stylesheet" />
  <style>
    @import url('https://rsms.me/inter/inter.css');

    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }

    body {
      font-feature-settings: "cv03", "cv04", "cv11";
    }
  </style>
  @stack('styles')
</head>

<body>
  <script src="{{asset('admin/assets/dist/js/demo-theme.min.js?1692870487')}}"></script>
  <div class="page">
    <!-- Sidebar -->
    @include('admin.layouts.sidebar')
    <!-- Navbar -->
    @include('admin.layouts.header')
    <div class="page-wrapper">
      @if (session('warning'))
        <div class="container-xl pt-3">
          <div class="alert alert-warning alert-dismissible" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
          </div>
        </div>
      @endif
      @yield('content')
      <!-- Footer -->
      @include('admin.layouts.footer')
    </div>
  </div>

  <!-- Modals -->
  <!-- Libs JS -->
  <!-- Tabler Core -->
  <script src="{{asset('admin/assets/dist/js/tabler.min.js?1692870487')}}" defer></script>
  <script src="{{asset('admin/assets/dist/js/demo.min.js?1692870487')}}" defer></script>
  @stack('scripts')
  @include('components.admin.off-canvas.patients_edit')
</body>

</html>