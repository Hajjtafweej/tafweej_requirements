@extends('layouts.parent')
@section('layout_content')
<div class="auth-header">
  <div class="header-has-submenu lang-submenu mx-1" ng-click="toggleLang = !toggleLang" ng-class="{'active': toggleLang}"><a class="btn btn-light" ng-class="{'active': toggleLang}"><i class="icon ic-globe"></i>
    {{ ['en' => 'English','ar' => 'عربي','fr' => 'Français'][LaravelLocalization::getCurrentLocale()] }}<i class="ic-caret-down"></i></a>
    <ul class="submenu">
      <li><a href="{{ url('ar') }}">عربي</a></li>
      <li><a href="{{ \LaravelLocalization::getLocalizedURL('en', \URL::current()) }}">English</a></li>
      <li><a href="{{ \LaravelLocalization::getLocalizedURL('fr', \URL::current()) }}">Français</a></li>
    </ul>
  </div>
  </div>
  <div class="page-container auth-page">
    <div class="container">
      <div class="auth-title">@lang('master.gate_title')</div>
      <div class="auth-subtitle">@lang('master.gate_subtitle')</div>
      @yield('content')
    </div>
  </div>
  <div class="footer auth-footer text-center">
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
  @endsection
