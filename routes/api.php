<?php

use Illuminate\Http\Request;
$auth_middleware = (request()->header('is_mobile')) ? 'jwt.auth' : 'auth';

Route::group(['namespace' => 'API'],function(){
  Route::post('login', 'AuthController@postLogin');
});
Route::group(['middleware' => $auth_middleware],function(){

  Route::group(['prefix' => 'country','namespace' => 'Country'],function(){
    Route::group(['prefix' => 'survey'],function(){
      Route::get('/list','Country_SurveyController@getList');
      Route::get('/show/{id}','Country_SurveyController@getShow');
      Route::post('/answer/{id}','Country_SurveyController@postAnswer');
      Route::get('/main-section/{id}','Country_SurveyController@getMainSectionDetails');
    });

    Route::group(['prefix' => 'presentation'],function(){
      Route::get('/list','Country_PresentationController@getList');
      Route::get('/show/{id}','Country_PresentationController@getShow');
      Route::post('/download/{id}','Country_PresentationController@getDownload');
    });

    Route::group(['prefix' => 'gallery'],function(){
      Route::get('/list','Country_GalleryController@getList');
      Route::get('/show/{id}','Country_GalleryController@getShow');
      Route::get('/recent','Country_GalleryController@getRecentUploads');
    });
  });

  Route::group(['prefix' => 'admin','middleware' => 'admin'],function(){

    Route::group(['prefix' => 'dashboard','middleware' => 'can:dashboard'],function(){
      Route::get('/statistics','Admin_DashboardController@getStatistics');
    });

    Route::group(['prefix' => 'user'],function(){
      Route::get('/show/{id}','Admin_UserController@getShow');
      Route::post('/add','Admin_UserController@Save');
      Route::put('/update/{id}','Admin_UserController@Save');
      Route::delete('/delete/{id}','Admin_UserController@Delete');
    });

    Route::group(['prefix' => 'helpers'],function(){
      Route::get('/list/{type}','Admin_HelpersController@getList');
    });

  });
});
