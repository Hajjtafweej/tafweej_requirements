@extends('layouts.parent')
@section('layout_content')
<div class="page-container auth-page">
  <div class="container">
    <div class="auth-title">بوابة التنسيق والتواصل للمنظومة الشاملة للتفويج</div>
    <div class="auth-subtitle">مكاتب شؤون الحجاج</div>
    @yield('content')
  </div>
</div>
<div class="footer auth-footer text-center">
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
@endsection
