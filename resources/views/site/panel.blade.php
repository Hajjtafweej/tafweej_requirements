<!DOCTYPE html>
<html ng-app="App" lang="{{ LaravelLocalization::getCurrentLocale() }}" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}" ng-controller="MainCtrl">
<head>
  <title>@lang('master.gate_title')</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  @if(LaravelLocalization::getCurrentLocaleDirection() == 'ltr')
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
  @endif
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
  <script src="{{ asset('assets/js/panel/ctrls/home.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/modules/gallery.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/modules/survey.js?v='.config('app.app_asset_ver')) }}"></script>
  <script src="{{ asset('assets/js/panel/modules/presentation.js?v='.config('app.app_asset_ver')) }}"></script>

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
  window.current_lang = '{{ LaravelLocalization::getCurrentLocale() }}';
  window.lang_dir = '{{ LaravelLocalization::getCurrentLocaleDirection() }}';
  window.lang_properties = {
    short_side: '{{ (LaravelLocalization::getCurrentLocaleDirection() == 'ltr') ? 'l' : 'r' }}',
    short_ops_side: '{{ (LaravelLocalization::getCurrentLocaleDirection() == 'ltr') ? 'r' : 'l' }}',
    side: '{{ (LaravelLocalization::getCurrentLocaleDirection() == 'ltr') ? 'left' : 'right' }}',
    ops_side: '{{ (LaravelLocalization::getCurrentLocaleDirection() == 'ltr') ? 'right' : 'left' }}'
  }
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
                <div class="header-has-submenu lang-submenu mx-1" ng-click="toggleLang = !toggleLang" ng-class="{'active': toggleLang}"><a class="btn btn-light btn-lang" ng-class="{'active': toggleLang}"><i class="icon ic-globe"></i>
                  <span class="d-none d-lg-inline-block">{{ ['en' => 'English','ar' => 'عربي','fr' => 'Français'][LaravelLocalization::getCurrentLocale()] }}</span><i class="ic-caret-down"></i></a>
                  <ul class="submenu">
                    <li><a href="{{ url('ar/panel') }}">عربي</a></li>
                    <li><a href="{{ url('en/panel') }}">English</a></li>
                    <li><a href="{{ url('fr/panel') }}">Français</a></li>
                  </ul></div>
                  <!-- <a class="btn btn-outline-primary rounded" href="#/office/home">معلومات المكتب</a> -->
                  <a class="btn btn-outline-light rounded" href="/logout">@lang('auth.logout')</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="links">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-xl-{{ (LaravelLocalization::getCurrentLocale() == 'fr') ? '12' : '9' }}">
                <ul findactivetab="1">
                  <li><a href="#/home"><i class="ic-home"></i>@lang('master.home_page')</a></li>
                  @if(!user()->Role || user()->Role->is_allow_presentations)
                  <li><a href="#/presentations"><i class="ic-presentation"></i>@lang('master.presentations')</a></li>
                  @endif
                  @if(!user()->Role || user()->Role->is_allow_gallery)
                  <li uib-tooltip="@lang('master.soon')"><a href="#/gallery"><i class="ic-photo-gallery"></i>@lang('master.gallery.title')</a></li>
                  @endif
                  @if(!user()->Role || user()->Role->is_allow_survey)
                  <li><a href="#/surveys"><i class="ic-checklist"></i>@lang('master.surveys')</a></li>
                  @endif
                  @if(!user()->Role || user()->Role->is_allow_tafweej_plans)
                  <li uib-tooltip="@lang('master.soon')"><a href="#/tafweej-plans"><i class="ic-plan"></i>@lang('master.tafweej_plans')</a></li>
                  @endif
                  @if(!user()->Role || user()->Role->is_allow_tafweej_tables)
                  <li uib-tooltip="@lang('master.soon')"><a href="#/tafweej-tables"><i class="ic-table"></i>@lang('master.tafweej_tables')</a></li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>
        <!-- Header Gallery -->
        @if(!user()->Role || user()->Role->is_allow_gallery)
        <home-gallery ng-if="isHomePage"></home-gallery>
        @endif
        <!-- End Header Gallery -->
      </div>
      <div class="page-content page-container">
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
            <div class="col-md-auto {{ (LaravelLocalization::getCurrentLocaleDirection() == 'rtl') ? 'mr' : 'ml' }}-md-auto">
              <img class="img-fluid" src="{{ asset('assets/images/footer-logos.png') }}" alt="">
            </div>
          </div>
        </div>
      </div>
  </div>
  <!-- Test Embed Code -->
<script>
	window.fwSettings={
	'widget_id':61000000505,
	'locale': '{{ LaravelLocalization::getCurrentLocale() }}'
	};
	
	!function(){if("function"!=typeof window.FreshworksWidget){var n=function(){n.q.push(arguments)};n.q=[],window.FreshworksWidget=n}}() 
  FreshworksWidget('identify', 'ticketForm', {
    custom_fields: {
      cf_username: "{{ user()->username }}",
      cf_user_type: "{{ user()->Role->name }}"
    }
});
	FreshworksWidget("setLabels", {
          '{{ LaravelLocalization::getCurrentLocale() }}': {
            banner: "@lang('master.contact_widget.banner')",
            launcher: "@lang('master.contact_widget.help')",
            contact_form: {
              title: "@lang('master.contact_widget.send_message')",
              submit: "@lang('master.contact_widget.submit')",
              confirmation: "@lang('master.contact_widget.confirmation')"
            }}});
</script>
<script type='text/javascript' src='https://widget.freshworks.com/widgets/61000000505.js' async defer></script>
  <!-- End Test Embed Code -->
  </body>
  </html>
