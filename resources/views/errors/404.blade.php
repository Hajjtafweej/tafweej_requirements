@extends('layouts.auth')
@section("title",__('master.page_not_found'))
@section('content')
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="auth-panel">
      <div class="text-center mb-5 pb-5">
        <h1 style="font-size: 100px;font-size: 100px;">404</h1>
        <p>@lang('master.page_not_found')</p>
        <hr>
        <a href="{{ route('login') }}" class="btn btn-primary">@lang('auth.back_to_login') <i class="ic-caret-left mr-2"></i></a>
      </div>
    </div>
  </div>
</div>
@endsection
