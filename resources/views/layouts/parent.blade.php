<!DOCTYPE html>
<html ng-app="App" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  @if(LaravelLocalization::getCurrentLocaleDirection() == 'ltr')
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
  @endif
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ (url()->current() == url('/')) ? $__env->yieldContent('title') : (trim($__env->yieldContent('title')) ? $__env->yieldContent('title') : 'بوابة التنسيق والتواصل للمنظومة الشاملة للتفويج') }}</title>
  @if (trim($__env->yieldContent('meta_description')))
  <meta name="description" content="@yield('meta_description')" />
  @endif
  @if (trim($__env->yieldContent('meta_keywords')))
  <meta name="keywords" content="@yield('meta_keywords')" />
  @endif
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:image" content="{{ asset('assets/images/cards/facebook-2x.png') }}" />
  <meta property="og:title" content="{{ (url()->current() == url('/')) ? $__env->yieldContent('title') : (trim($__env->yieldContent('title')) ? 'بوابة مكاتب  | '.$__env->yieldContent('title') : 'App') }}"/>
  <meta property="og:image:width" content="256"/>
  <meta property="og:image:height" content="256"/>
  <meta name="twitter:title" content="{{ (url()->current() == url('/')) ? $__env->yieldContent('title') : (trim($__env->yieldContent('title')) ? 'App | '.$__env->yieldContent('title') : 'App') }}" />
  <meta name="twitter:image:src" content="{{ asset('assets/images/cards/facebook-2x.png') }}" />
  <!-- Styles -->
  <link href="{{ asset('assets/css/reset.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/icons.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/plugins.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/app.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/responsive.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16 32x32 48x48 64x64" type="image/vnd.microsoft.icon"/>
  <script type="text/javascript">
  var baseUrl = "{{ env('APP_URL') }}";
  </script>
</head>
<body>
  <flash-message duration="6000" show-close="true" on-dismiss="myCallback(flash)"></flash-message>
  @yield('layout_content')
  @yield('scripts')
  <script src="{{ asset('assets/js/site/intlTellInput/utils.js') }}"></script>
  @if(domain() != 'localhost')
  <script src="{{ asset('assets/js/site/vendor.js?v='.config('app.asset_ver')) }}" type="text/javascript"></script>
  <script src="{{ asset('assets/js/site/app.js?v='.config('app.asset_ver')) }}" type="text/javascript"></script>
  <script src="{{ asset('assets/js/site/auth.js?v='.config('app.asset_ver')) }}" type="text/javascript"></script>
  @else
  <script src="{{ asset('assets/js/site/vendor.js?v='.config('app.asset_ver')) }}" type="text/javascript"></script>
  <script src="{{ asset('assets/js/site/app.js?v='.config('app.asset_ver')) }}" type="text/javascript"></script>
  <script src="{{ asset('assets/js/site/auth.js?v='.config('app.asset_ver')) }}" type="text/javascript"></script>
  @endif
  <script type="text/javascript">
  window.angularjsPlugins = [];
  window.lang = '{{ LaravelLocalization::getCurrentLocale() }}';
  </script>
</body>
</html>
