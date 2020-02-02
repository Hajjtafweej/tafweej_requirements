<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SurveyQuestionOption extends Model
{
  use SoftDeletes;
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
