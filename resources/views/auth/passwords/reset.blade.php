@extends('layouts.auth')
@section("title",__('auth.update_password'))
@section('content')
<form method="POST" action="{{ route('password.update') }}">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="auth-panel">
        <h1 class="text-center">@lang('auth.update_password')</h1>
        @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
        @endif
        <div class="form-group form-group-lined form-group-lg">
          <div class="input-icon">
            <i class="ic-envelope"></i>
            <input id="email" type="email" placeholder="@lang('master.email')" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>
          </div>
          @if ($errors->has('email'))
          <span class="invalid-feedback">
            <strong>{{ $errors->first('email') }}</strong>
          </span>
          @endif
        </div>
        <div class="form-group form-group-lined form-group-lg">
          <div class="input-icon">
            <i class="ic-lock"></i>
            <input id="password" placeholder="@lang('auth.new_password')" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
          </div>
          @if ($errors->has('password'))
          <span class="invalid-feedback">
            <strong>{{ $errors->first('password') }}</strong>
          </span>
          @endif
        </div>
        <div class="form-group form-group-lined form-group-lg">
          <div class="input-icon">
            <i class="ic-lock"></i>
            <input id="password-confirm" placeholder="@lang('auth.confirm_password')" type="password" class="form-control" name="password_confirmation" required>
          </div>
        </div>
        <div class="pt-3">
          <button type="submit" class="btn btn-primary btn-lg btn-block">@lang('master.save')</button>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection
