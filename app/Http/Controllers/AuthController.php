<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB,Auth,Validator;
use JWTAuth;
use \GeniusTS\HijriDate\Hijri as Hijri;
class AuthController extends Controller
{

    /**
    * API Login, on success return JWT Auth token
    *
    * @param Request $q
    * @return \Illuminate\Http\JsonResponse
    */
    public function postLogin(Request $q)
    {

      $credentials = $q->only('username', 'password');

      $rules = [
      'username' => 'required',
      'password' => 'required',
      ];
      $validator = Validator::make($credentials, $rules);
      if($validator->fails()) {
        return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
      }
      if($q->is_web){
        $User = User::where('username',$q->username)->select('id','username','email','password')->first();
        if($User && \Hash::check($q->password,$User->password)){
          Auth::login($User);
          return response()->json(['message' => 'logged_in']);
        }else {
          return response()->json(['message' => 'login_failed']);
        }
      }else {
        try {
          // attempt to verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'we_cant_find_account']);
          }
        } catch (JWTException $e) {
          return response()->json(['message' => 'login_failed']);
        }

        return response()->json(['message' => 'logged_in','token' => $token,'expires_in' => (auth('api')->factory()->getTTL() * 60),'user' => \Auth::User()], 200);
      }
    }
}
