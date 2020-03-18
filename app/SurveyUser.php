<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyUser extends Model
{
  protected $guarded = ['id'];

  /**
  * Survey
  *
  */
  public function Survey(){
    return $this->belongsTo('App\Survey');
  }

  /**
  * User
  *
  */
  public function User(){
    return $this->belongsTo('App\User');
  }

}
