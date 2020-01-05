<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyAnswerValue extends Model
{
  protected $guarded = ['id'];
  protected $casts = [
    'survey_question_option_id' => 'integer'
  ];
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
    return $this->belongsTo('App\SurveyQuestion','survey_question_id','id');
  }

  /**
  * Survey Answer
  *
  */
  public function SurveyAnswer(){
    return $this->belongsTo('App\SurveyAnswer');
  }


}
