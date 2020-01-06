<?php

namespace App\Http\Middleware;

use Closure;

class HttpsMiddleware
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
    // if (!$request->secure() && domain() != 'localhost') {
    //
    //   return redirect()->secure(str_replace('crowd/','',$request->getRequestUri()));
    //
    // }
    return $next($request);
  }
}
