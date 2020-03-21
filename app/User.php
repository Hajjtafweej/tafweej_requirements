<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use \GeniusTS\HijriDate\Hijri as Hijri;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable implements JWTSubject
{
  use SoftDeletes;
  use Notifiable;

  protected $table = 'users';

  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'name', 'email', 'password','phone'
  ];

  /**
  * With relationship in serialization
  *
  * @var array
  */
  protected $with = [

  ];

  /**
  * Append attributes
  *
  * @var array
  */
  protected $appends = [

  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [
    'password', 'remember_token'
  ];

  /**
  * Set casts
  *
  * @var array
  */
  protected $casts = [
      'is_admin_permissions' => 'object',
      'is_admin' => 'integer',
      'is_supervisor' => 'integer'
  ];

  /**
  * Get the identifier that will be stored in the subject claim of the JWT.
  *
  * @return mixed
  */
  public function getJWTIdentifier()
  {
    return $this->getKey();
  }
  /**
  * Return a key value array, containing any custom claims to be added to the JWT.
  *
  * @return array
  */
  public function getJWTCustomClaims()
  {
    return [];
  }


}
