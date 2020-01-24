<?php

use Illuminate\Http\Request;

Route::group(['namespace' => 'API'],function(){
  Route::post('login', 'AuthController@postLogin');
});
Route::group(
  [
    'middleware' => [ 'api-localization' ]
  ],
  function(){
    $auth_middleware = (request()->header('is_mobile')) ? 'jwt.auth' : 'auth';
    Route::group(['middleware' => $auth_middleware],function(){

      Route::group(['prefix' => 'country','namespace' => 'Portal'],function(){
        Route::group(['prefix' => 'survey'],function(){
          Route::get('/list','PortalSurveyController@getList');
          Route::get('/show/{id}','PortalSurveyController@getShow');
          Route::post('/answer/{id}','PortalSurveyController@postAnswer');
          Route::get('/main-section/{id}','PortalSurveyController@getMainSectionDetails');
        });

        Route::group(['prefix' => 'presentation'],function(){
          Route::get('/list','PortalPresentationController@getList');
          Route::get('/show/{id}','PortalPresentationController@getShow');
          Route::post('/download/{id}','PortalPresentationController@getDownload');
        });

        Route::group(['prefix' => 'gallery'],function(){
          Route::get('/list','PortalGalleryController@getList');
          Route::get('/show/{id}','PortalGalleryController@getShow');
          Route::get('/recent','PortalGalleryController@getRecentUploads');
        });
      });

      Route::group(['prefix' => 'admin','middleware' => 'admin'],function(){

        Route::group(['prefix' => 'dashboard','middleware' => 'can:dashboard'],function(){
          Route::get('/statistics','AdminDashboardController@getStatistics');
        });

        Route::group(['prefix' => 'user'],function(){
          Route::get('/show/{id}','AdminUserController@getShow');
          Route::post('/add','AdminUserController@Save');
          Route::put('/update/{id}','AdminUserController@Save');
          Route::delete('/delete/{id}','AdminUserController@Delete');
        });

        Route::group(['prefix' => 'helpers'],function(){
          Route::get('/list/{type}','AdminHelpersController@getList');
        });

      });
    });
  });
