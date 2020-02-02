<!DOCTYPE html>
<html ng-app="App" lang="ar" dir="rtl" ng-controller="MainCtrl">
<head>
  <title>لوحة التحكم - @lang('master.gate_title')</title>
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

  <script src="{{ asset('assets/js/admin/plugins.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/app.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/filters.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/directives.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/modules/survey.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/modules/user.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/ctrls/dashboard.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/admin/ctrls/datatable.js?v='.config('app.app_asset_ver')) }}"></script>
  <script>
  var assets_ver = {{ config('app.app_asset_ver') }};
  window.auth = {!! auth()->user()->toJson() !!};
  window.lists = {!! collect(Lang::get('lists'))->map(function ($item,$key) {
    if (in_array($key,['makkah_towns','madinah_towns'])) {
      return collect($item)->sortBy(function ($town){
        return $town;
      });
    }else {
      return $item;
    }
  })->toJson() !!};
  window.lang = {!! collect(Lang::get('master'))->toJson() !!};
  window.current_lang = 'ar';
  window.lang_dir = 'rtl';
</script>
</head>
<body class="light-bg">
  <flash-message duration="2000" show-close="true" on-dismiss="myCallback(flash)"></flash-message>
  <div class="page-layout">
    <div class="header">
      <div class="top">
        <div class="sidebar-toggle d-lg-none"><i class="ic-menu2"></i></div>
        <div class="container">
          <div class="row align-items-center">
            <div class="col">
              <a class="logo" href="#/"><img src="{{ asset('assets/images/logo_'.LaravelLocalization::getCurrentLocale().'.png?v='.config('app.app_asset_ver')) }}" alt=""></a>
            </div>
            <div class="col-auto mr-auto">
              <div class="header-user-nav d-flex align-items-center">
                <a class="btn btn-outline-light rounded" href="/logout">تسجيل الخروج</a>
              </div>
            </div>
          </div>
        </div>
        <div class="links pb-3">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-xl-9">
                <ul findactivetab="2">
                  <!-- <li><a href="#/admin/dashboard"><i class="ic-bar-chart"></i>الأحصائيات</a></li>-->
                  <!-- <li><a href="#/admin/presentations"><i class="ic-presentation"></i>العروض المرئية</a></li>
                  <li><a href="#/admin/gallery"><i class="ic-photo-gallery"></i>الصور والفيديو</a></li> -->
                  <li><a href="#/admin/surveys"><i class="ic-checklist"></i>الأستبانات</a></li>
                  <li><a href="#/admin/users"><i class="ic-user-2"></i>المستخدمين</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="page-content">
      <div autoscroll="true" ng-cloak ng-view bs-affix-target init-ripples>
      </div>
    </div>
    <div class="footer text-center">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-auto">
            <div class="d-none d-md-block">
              @lang('master.copyright_text',['year' => date('Y')])
            </div>
          </div>
          <div class="col-md-auto mr-md-auto">
            <img class="img-fluid" src="{{ asset('assets/images/footer-logos.png') }}" alt="">
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
