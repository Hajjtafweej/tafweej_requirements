<?php
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('register', function(){
  return redirect('/login');
});
Route::group(
  [
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
  ],
  function(){
    /* Start Auth routes */
    Auth::routes();
    Route::get('/',function(){
      return redirect('/login');
    })->middleware('guest')->name('home_page');
    /* Start Printable assets */
    Route::group(['prefix' => 'print','middleware' => 'auth'],function(){
      Route::get('participant/{id}','Admin\AdminParticipantController@getPrint');
    });
  });
  /* Web API */
  Route::group(['prefix' => 'api'],function(){
    Route::group(['prefix' => 'web'],function(){
      Route::group(['prefix' => 'admin','namespace' => 'Admin','middleware' => ['auth','admin']],function(){
        Route::post('/dt/{module}','AdminDatatableController@getModule');
        Route::post('/dt/{module}/{sub_module}','AdminDatatableController@getModule');
        Route::post('/dt/{module}/{sub_module}/{module_id}','AdminDatatableController@getModule');
        Route::post('/dt/{module}/{sub_module}/{module_id}/{sub_module_id}','AdminDatatableController@getModule');
        Route::get('/export/{module}','AdminDatatableController@getModule')->name('export');
        Route::get('/export/{module}/{sub_module}','AdminDatatableController@getModule')->name('export');
      });

      Route::group(['prefix' => 'auth'],function(){
        Route::post('login', 'AuthController@postLogin');
      });
    });
  });
