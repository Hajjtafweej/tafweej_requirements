<!DOCTYPE html>
<html ng-app="App" dir="rtl" ng-controller="MainCtrl">
<head>
  <title>بوابة مكاتب شؤون الحجاج</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <link href="{{ asset('assets/css/reset.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/icons.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/plugins.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/app.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <link href="{{ asset('assets/css/responsive.css?v='.config('app.asset_ver')) }}" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
  var baseUrl = "{{ env('APP_URL') }}";
  var curHijriYear = {{ \GeniusTS\HijriDate\Date::today()->format('Y') }};
  var appSettings = {!! collect(config('app.app_settings'))->toJson() !!};
  </script>
  <script src="{{ asset('assets/js/panel/plugins.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/app.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/filters.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/directives.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/modules/gallery.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/modules/survey.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/ctrls/dashboard.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/ctrls/home.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/ctrls/datatable.js?v='.config('app.app_asset_ver')) }}"></script>
  <script>
  var assets_ver = {{ config('app.app_asset_ver') }};
  window.auth = {!! auth()->user()->toJson() !!};
  window.lists = {!! collect(Lang::get('lists'))->toJson() !!};
  </script>
</head>
<body class="light-bg">
  <flash-message duration="2000" show-close="true" on-dismiss="myCallback(flash)"></flash-message>
  <div class="header">
    <div class="top">
      <div class="sidebar-toggle d-lg-none"><i class="ic-menu"></i></div>
      <div class="container">
        <div class="row align-items-center">
          <div class="col">
            <a class="logo" href="#/"><img src="{{ asset('assets/images/logo.png?v='.config('app.app_asset_ver')) }}" alt=""></a>
          </div>
          <div class="col-auto mr-auto">
            <!-- <a class="btn btn-outline-primary rounded" href="#/office/home">معلومات المكتب</a> -->
            <a class="btn btn-outline-light rounded" href="/logout">تسجيل الخروج</a>
          </div>
        </div>
      </div>
    </div>
    <div class="links">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-9">
            <ul findactivetab="1">
              <li><a href="#/home"><i class="ic-home"></i>الرئيسية</a></li>
              <li><a href="#/surveys"><i class="ic-checklist"></i>الاستبانات</a></li>
              <li uib-tooltip="قريباً"><a href="#/gallery"><i class="ic-photo-gallery"></i>الصور والفيديو</a></li>
              <li uib-tooltip="قريباً"><a href="#/meetings"><i class="ic-meeting"></i>محاضر الاجتماعات</a></li>
              <li uib-tooltip="قريباً"><a href="#/presentations"><i class="ic-file"></i>العروض المرئية</a></li>
              <li uib-tooltip="قريباً"><a href="#/tafweej-tables"><i class="ic-table"></i>جداول التفويج</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- Header Gallery -->
    <home-gallery></home-gallery>

    <!-- End Header Gallery -->
  </div>
  <div class="page-container">
    <div autoscroll="true" ng-cloak ng-view bs-affix-target init-ripples>
    </div>
  </div>
  <div class="footer text-center">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-auto">
          <div class="d-none d-md-block">
            جميع الحقوق محفوظة {{ date('Y') }}
          </div>
        </div>
        <div class="col-md-auto mr-md-auto">
          <img class="img-fluid" src="{{ asset('assets/images/footer-logos.png') }}" alt="">
        </div>
      </div>
    </div>
  </div>
</body>
</html>
