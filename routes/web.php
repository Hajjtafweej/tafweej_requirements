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
    /* Start Downloadable assets */
    Route::group(['prefix' => 'download','middleware' => 'auth'],function(){
      Route::get('file/{folder}/{file}','SiteController@getDownloadFile');
      Route::get('presentation/{id}','Portal\PortalPresentationController@getDownload');
    });
    /* Start External Pages */
    Route::get('page/{slug}','SiteController@getExternalPage');
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

      Route::group(['namespace' => 'Portal'],function(){
        Route::post('apply-to-portal', 'PortalHelpersController@postApplyToPortal');
      });

      Route::group(['prefix' => 'auth'],function(){
        Route::post('login', 'AuthController@postLogin');
      });
    });
  });
