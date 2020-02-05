<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SurveyQuestion extends Model
{
  use SoftDeletes;
  protected $guarded = ['id'];
  protected $casts = [
    'is_has_notes' => 'integer',
    'survey_section_id' => 'integer',
    'main_section_id' => 'integer',
    'type_options' => 'object'
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
    return $this->hasOne('App\SurveyAnswerValue')->orderBy('id','DESC');
  }


}
