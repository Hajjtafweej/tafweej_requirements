<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
  protected $guarded = ['id'];
  protected $casts = [
    'is_has_notes' => 'integer',
    'survey_section_id' => 'integer',
    'main_section_id' => 'integer'
  ];
  /**
  * Survey
  *
  */
  public function Survey(){
    return $this->belongsTo('App\Survey');
  }

  /**
  * Section
  *
  */
  public function Section(){
    return $this->belongsTo('App\SurveySection');
  }

  /**
  * Options
  *
  */
  public function Options(){
    return $this->hasMany('App\SurveyQuestionOption');
  }

  /**
  * Last Answer value:
  * used in country survey page to get the last version of answer for each question
  */
  public function LastAnswerValue(){
    return $this->hasOne('App\SurveyAnswerValue')->where('user_id',user()->id)->orderBy('id','DESC');
  }


}
