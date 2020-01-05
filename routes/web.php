<?php
Auth::routes();
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('register', function(){
  return redirect('/login');
});
Route::get('forget-password', '\App\Http\Controllers\Auth\LoginController@forgetPassword');

Route::get('/',function(){
  return redirect('/login');
})->middleware('guest')->name('home_page');

/* Web API */
Route::group(['prefix' => 'api'],function(){
  Route::group(['prefix' => 'web'],function(){
    Route::group(['prefix' => 'auth'],function(){
      Route::post('login', 'AuthController@postLogin');
    });
  });
});
