<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
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
  * Survey Answer Values
  *
  */
  public function Values(){
    return $this->hasMany('App\SurveyAnswerValue');
  }

}
