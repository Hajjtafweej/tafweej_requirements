<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyLog extends Model
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


  /**
  * Log a date of actions on the survey by user himself such as when the survey has been started etc.
  *
  * @param Eloquent $query
	* @param integer $survey_id
	* @param integer $action view,answer
	* @param boolean $is_completed check if the survey completed
	* @param mixed $user_id
  * @return Eloquent
  */
  public function scopeLog($query,$survey_id,$action,$is_completed = false,$user_id = null)
  {
    $logDatetime = date('Y-m-d H:i:s');
    if ($action == 'view') {
      return $query->firstOrCreate(['survey_id' => $survey_id,'user_id' => ($user_id ?? user()->id)],['viewed_at' => $logDatetime]);
    }else {
      $query = $query->where([['survey_id',$survey_id],['user_id',($user_id ?? user()->id)]])->first();
      if ($query) {
        $query->last_answer_at = $logDatetime;
        if (!$query->started_at) {
          $query->started_at = $logDatetime;
        }
        $surveyCompletion = \App\Survey::where('id',$survey_id)->calculateCompletion(($user_id ?? user()->id))->first();
        if ($surveyCompletion->questions_count == $surveyCompletion->completed_questions_count && !$query->completed_at) {
          $query->completion_rate = 100;
          $query->completed_at = $logDatetime;
        }else {
          $query->completion_rate = ($surveyCompletion->completed_questions_count/$surveyCompletion->questions_count)*100;
        }
        $query->save();
      }
      return $query;
    }
  }

}
