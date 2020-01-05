<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Survey extends Model
{
  protected $table = 'surveys';
  protected $guarded = ['id'];

  /**
  * Main Sections
  *
  */
  public function MainSections(){
    return $this->hasMany('App\SurveySection')->where('parent_id',0);
  }

  /**
  * Answers
  *
  */
  public function Answers(){
    return $this->belongsTo('App\SurveyAnswer');
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
  * Last Answer
  *
  */
  public function LastAnswer(){
    return $this->hasOne('App\SurveyAnswer')->orderBy('id','DESC');
  }

  /**
  * Only active surveys
  *
  */
  public function scopeOnlyActive($query)
  {
    return $query->where('is_active', 1);
  }

  /**
  * Calculate the completion
  *
  */
  public function scopeCalculateCompletion($query,$user_id)
  {
    return $query->withCount(['Questions','CompletedQuestions' => function($CompletedQuestions){
			return $CompletedQuestions->whereHas('LastAnswerValue',function($LastAnswerValue){
				return $LastAnswerValue->where('user_id',user()->id);
			});
		}]);
  }

}
