@extends('layouts.parent')
@section('layout_content')
<div class="auth-header"></div>
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
