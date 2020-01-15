<?php

namespace App\Http\Middleware;

use Closure;

class ApiLocalizationMiddleware
{
  /**
  * Handle an incoming request.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure  $next
  * @return mixed
  */
  public function handle($request, Closure $next)
  {
    // default to app.locale if no locale header is set on the request
    app()->setLocale($request->header('X-App-Locale') ?: config('app.locale'));
    return $next($request);
  }
}
