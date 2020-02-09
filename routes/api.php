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

      Route::group(['prefix' => 'portal','namespace' => 'Portal'],function(){
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

      Route::group(['prefix' => 'admin','namespace' => 'Admin','middleware' => 'admin'],function(){

        Route::group(['prefix' => 'dashboard','middleware' => 'can:admin_dashboard'],function(){
          Route::get('/statistics','AdminDashboardController@getStatistics');
        });

        Route::group(['prefix' => 'survey'],function(){
          Route::post('/add','AdminSurveyController@saveInfo');
          Route::put('/update-info/{id}','AdminSurveyController@saveInfo');
          Route::get('/show/{id}','AdminSurveyController@getShow');
          Route::get('/main-section/{id}','AdminSurveyController@getMainSectionDetails');
          Route::delete('/delete/{id}','AdminSurveyController@deleteSurvey');
          Route::put('/activation/{id}','AdminSurveyController@Activation');
          Route::get('/export/{id}','AdminSurveyController@exportSurveyAnswers');

          Route::group(['prefix' => 'section'],function(){
            Route::post('/add','AdminSurveyController@saveSection');
            Route::put('/update/{id}','AdminSurveyController@saveSection');
            Route::delete('/delete/{id}','AdminSurveyController@deleteSection');
          });

          Route::group(['prefix' => 'question'],function(){
            Route::post('/add','AdminSurveyController@saveQuestion');
            Route::put('/update/{id}','AdminSurveyController@saveQuestion');
            Route::delete('/delete/{id}','AdminSurveyController@deleteQuestion');
          });

        });

        Route::group(['prefix' => 'user','middleware' => 'can:admin_manage_users'],function(){
          Route::get('/show/{id}','AdminUserController@getShow');
          Route::post('/add','AdminUserController@Save');
          Route::put('/update/{id}','AdminUserController@Save');
          Route::delete('/delete/{id}','AdminUserController@Delete');

          Route::group(['prefix' => 'role'],function(){
            Route::get('/show/{id}','AdminUserController@getShowRole');
            Route::post('/add','AdminUserController@saveRole');
            Route::put('/update/{id}','AdminUserController@saveRole');
            Route::delete('/delete/{id}','AdminUserController@deleteRole');
          });

          Route::group(['prefix' => 'registration'],function(){
            Route::delete('/delete/{id}','AdminUserController@deleteRegistration');
          });
        });

        Route::group(['prefix' => 'helpers'],function(){
          Route::get('/main-lists','AdminHelpersController@getMainLists');
          Route::get('/list/{type}','AdminHelpersController@getList');
        });

      });
    });
  });
