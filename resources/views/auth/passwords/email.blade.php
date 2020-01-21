@extends('layouts.auth')
@section("title",__('auth.forget_password'))
@section('content')
<form method="POST" action="{{ route('password.email') }}">
  @csrf
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="auth-panel">
        <h1 class="text-center mb-5">@lang('auth.forget_password')</h1>
        @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
        @endif
        <div class="form-group form-group-lined form-group-lg">
          <div class="input-icon">
            <i class="ic-envelope"></i>
            <input id="email" type="email" placeholder="@lang('master.email')" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" autofocus required>
          </div>
          @if ($errors->has('email'))
          <span class="invalid-feedback">
            <strong>{{ $errors->first('email') }}</strong>
          </span>
          @endif

        </div>
        <div class="pt-3">
        <button type="submit" class="btn btn-primary btn-lg btn-block rounded">@lang('master.send')</button>
      </div>
        <div class="text-center mt-4">
          <a class="btn p-0 text-muted" href="{{ route('login') }}">@lang('auth.back_to_login')</a>
        </div>
      </div>
    </div>
  </div>
  @endsection
