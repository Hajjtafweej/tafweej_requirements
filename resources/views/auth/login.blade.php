@extends('layouts.auth')
@section("title",'تسجيل الدخول')
@section('content')
<div ng-controller="LoginCtrl">
  <form method="POST" name="loginForm" id="loginForm">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="auth-panel">
          <div after-init-page="hide" class="auth-loading"></div>
          <div class="d-none" after-init-page="show">
            <div class="form-group form-group-lined form-group-lg">
              <div class="input-icon">
                <i class="ic-user"></i>
                <input type="text" class="form-control" placeholder="اسم المستخدم" required ng-class="{'is-invalid': form_errors.username.length || invalid_login}"  id="username" name="username" ng-model="login.username">
              </div>
              <div class="invalid-feedback" ng-class="{'d-block': invalid_login}">اسم المستخدم وكلمة السر غير صحيحين</div>
            </div>
            <div class="form-group form-group-lined auth-password-form form-group-lg mb-5">
              <div class="input-icon">
                <i class="ic-lock"></i>
                <input ng-class="{'is-invalid': form_errors.password.length}" placeholder="كلمة السر" id="password" ng-model="login.password" type="password" class="form-control" name="password" required>
              </div>
              <a href="{{ route('password.request') }}">نسيتها؟</a>
            </div>
            <div class="pt-3">
              <button type="submit" ng-disabled="isLoading" ng-click="sendLogin(loginForm.$valid)" class="btn btn-primary btn-lg btn-block rounded">دخول</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
