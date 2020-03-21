<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'auth'],function(){
  Route::post('login', 'AuthController@postLogin');
  Route::post('logout', 'AuthController@postLogout');
});
Route::group(
  [
    'middleware' => [ 'api-localization' ]
  ],
  function(){

    $auth_middleware = (request()->header('is_jwt')) ? 'jwt.auth' : 'auth';
    Route::group(['middleware' => $auth_middleware],function(){

      Route::group(['prefix' => 'auth'],function(){
        Route::get('me', 'AuthController@getMe');
      });


      Route::group(['prefix' => 'admin','namespace' => 'Admin','middleware' => 'admin'],function(){

        Route::group(['prefix' => 'dashboard','middleware' => 'can:admin_dashboard'],function(){
          Route::get('/statistics','AdminDashboardController@getStatistics');
        });


        Route::group(['prefix' => 'participant'],function(){
          Route::get('/show/{id}','AdminParticipantController@getShow');
          Route::get('/details/{id}','AdminParticipantController@getDetails');
          Route::post('/add','AdminParticipantController@Save');
          Route::post('/update/{id}','AdminParticipantController@Save');
          Route::post('/delete/{id}','AdminParticipantController@Delete');
          
          Route::group(['prefix' => 'requirement'],function(){
            Route::get('/show/{id}','AdminParticipantController@getShowRequirement');
            Route::post('/add','AdminParticipantController@saveRequirement');
            Route::post('/update/{id}','AdminParticipantController@saveRequirement');
            Route::post('/delete/{id}','AdminParticipantController@deleteRequirement');
          });
        });

        Route::group(['prefix' => 'helpers'],function(){
          Route::get('/main-lists','AdminHelpersController@getMainLists');
          Route::get('/list/{type}','AdminHelpersController@getList');
        });

      });
    });
  });
