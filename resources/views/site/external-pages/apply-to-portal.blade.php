@extends('layouts.auth')
@section("title",__('external.apply_to_portal_title'))
@section('content')
<div ng-controller="ApplyCtrl">
  <form method="POST" name="applyForm" id="applyForm">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="auth-panel">
          <h1 class="text-center mb-5">@lang('external.apply_to_portal_title')</h1>
          <div after-init-page="hide" class="auth-loading"></div>
          <div class="d-none" after-init-page="show">
            <div class="alert alert-success text-center" ng-show="sendSuccessed">
              @lang('external.apply_success')
            </div>
            <div class="form-group form-group-lined form-group-lg">
              <div class="input-icon">
                <i class="ic-globe"></i>
                <select class="form-control" required ng-model="apply.country_id">
                  <option value="" ng-disabled>@lang('master.form.choose_country')</option>
                  @foreach($ListOfCountries as $Country)
                  <option value="{{ $Country->id }}">{{ $Country->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group form-group-lined form-group-lg">
              <div class="input-icon">
                <i class="ic-user"></i>
                <input type="text" class="form-control ltr" placeholder="@lang('master.form.delegate_name')" required  id="delegation_name" name="delegation_name" ng-model="apply.delegation_name">
              </div>
            </div>
            <div class="form-group form-group-lined form-group-lg mb-5">
              <div class="input-icon">
                <i class="ic-envelope"></i>
                <input placeholder="@lang('master.form.email')" id="email" ng-model="apply.email" type="email" class="form-control ltr" name="email" required>
              </div>
            </div>
            <div class="form-group form-group-lined form-group-lg mb-5">
              <input type="text" id="phone" class="form-control" required ng-model="apply.phone" ng-intl-tel-input>
            </div>
            <div class="pt-3">
              <button type="submit" ng-disabled="isLoading" ng-loading="isLoading" ng-click="sendApply(applyForm.$valid)" class="btn btn-primary btn-lg btn-block rounded">@lang('master.send')</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  window.lang_phrases = {
    check_required_fields: '@lang('master.check_required_fields')'
  };
</script>
@endsection
