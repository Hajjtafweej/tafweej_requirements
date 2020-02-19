@extends('layouts.auth')
@section("title",__('external.under_maintenance_title'))
@section('content')
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="auth-panel">
      <div class="text-center pt-4">
        <h1>@lang('external.under_maintenance_title')</h1>
      </div>
    </div>
  </div>
</div>
@endsection
