<?php

/* Panel */
Route::group(
  [
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
  ],
  function(){
    Route::group(['prefix' => 'panel','middleware' => 'auth'],function(){
      Route::get('/','SiteController@getPanel');
      Route::group(['namespace' => 'Panel'],function(){
        /* Normal users */
        Route::get('/profile','App_StaffController@getProfile');
        /* Only for admins */
        Route::group(['middleware' => 'admin'],function(){
          /* Flow Plugin Uploader */
          Route::group(['prefix' => 'flow-uploader'], function () {
            Route::post('/import','App_FlowUploaderController@Import');
            Route::post('/start-import','App_FlowUploaderController@StartImport');
          });
          Route::get('/dashboard','App_DashboardController@getStatistics');
          Route::post('/dt/{module}','App_DatatableController@getModule');
          Route::post('/dt/{module}/{sub_module}/{module_id}','App_DatatableController@getModule');
          Route::post('/dt/{module}/{sub_module}/{module_id}/{sub_module_id}','App_DatatableController@getModule');
          Route::get('/export/{module}','App_DatatableController@getModule')->name('export');
        });
      });

    });
  });
