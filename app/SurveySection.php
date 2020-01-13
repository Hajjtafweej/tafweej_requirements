<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class SurveySection extends Model
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
  * Questions
  *
  */
  public function Questions(){
    return $this->hasMany('App\SurveyQuestion');
  }

  /**
  * Completed Questions
  *
  */
  public function CompletedQuestions(){
    return $this->hasMany('App\SurveyQuestion')->whereHas('LastAnswerValue');
  }

  /**
  * Calculate the completion
  *
  */
  public function scopeCalculateCompletion($query,$user_id)
  {
    return $query;
  }
}
