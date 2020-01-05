<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestionOption extends Model
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
  * Question
  *
  */
  public function Question(){
    return $this->belongsTo('App\SurveyQuestion');
  }


}
