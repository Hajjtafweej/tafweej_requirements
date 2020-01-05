@extends('layouts.auth')
@section("title",'الصفحة غير موجودة')
@section('content')
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="auth-panel">
      <div class="text-center mb-5 pb-5">
        <h1 style="font-size: 100px;font-size: 100px;">404</h1>
        <p>الصفحة غير موجودة</p>
        <hr>
        <a href="{{ route('login') }}" class="btn btn-primary">الذهاب الى صفحة الدخول <i class="ic-caret-left mr-2"></i></a>
      </div>
    </div>
  </div>
</div>
@endsection
