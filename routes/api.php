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
          Route::post('/update-info/{id}','AdminSurveyController@saveInfo');
          Route::get('/show/{id}','AdminSurveyController@getShow');
          Route::get('/main-section/{id}','AdminSurveyController@getMainSectionDetails');
          Route::post('/delete/{id}','AdminSurveyController@deleteSurvey');
          Route::post('/activation/{id}','AdminSurveyController@Activation');
          Route::get('/export/{id}','AdminSurveyController@exportSurveyAnswers');
          Route::post('/clone/{id}','AdminSurveyController@cloneSurvey');

          Route::group(['prefix' => 'section'],function(){
            Route::post('/add','AdminSurveyController@saveSection');
            Route::post('/update/{id}','AdminSurveyController@saveSection');
            Route::post('/delete/{id}','AdminSurveyController@deleteSection');
          });

          Route::group(['prefix' => 'question'],function(){
            Route::post('/add','AdminSurveyController@saveQuestion');
            Route::post('/update/{id}','AdminSurveyController@saveQuestion');
            Route::post('/delete/{id}','AdminSurveyController@deleteQuestion');
          });

        });

        Route::group(['prefix' => 'user','middleware' => 'can:admin_manage_users'],function(){
          Route::get('/show/{id}','AdminUserController@getShow');
          Route::post('/add','AdminUserController@Save');
          Route::post('/update/{id}','AdminUserController@Save');
          Route::post('/delete/{id}','AdminUserController@Delete');

          Route::group(['prefix' => 'role'],function(){
            Route::get('/show/{id}','AdminUserController@getShowRole');
            Route::post('/add','AdminUserController@saveRole');
            Route::post('/update/{id}','AdminUserController@saveRole');
            Route::post('/delete/{id}','AdminUserController@deleteRole');
          });

          Route::group(['prefix' => 'registration'],function(){
            Route::post('/delete/{id}','AdminUserController@deleteRegistration');
          });
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
