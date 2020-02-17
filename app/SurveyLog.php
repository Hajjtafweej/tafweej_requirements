<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SurveyLog extends Model
{
  protected $table = 'survey_logs';
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
  * Get only completed surveys
  *
  * @param Eloquent $query
  * @return Eloquent
  */
  public function scopeOnlyCompleted($query)
  {
    return $query->whereRaw($this->table.'.completed_at IS NOT NULL');
  }

  /**
  * Get only logs which belongs to users already started answer not just view it
  *
  * @param Eloquent $query
  * @return Eloquent
  */
  public function scopeOnlyStarted($query)
  {
    return $query->whereRaw($this->table.'.started_at IS NOT NULL');
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
          $query->completion_rate = round((($surveyCompletion->completed_questions_count/$surveyCompletion->questions_count)*100),2);
        }
        $query->save();

        /* Start send email to executives when the user complete all questions of survey */
        if($query->completion_rate == 100){
          \Mail::to(config('app.app_settings.survey_completed_recipents_emails'))->send(new \App\Mail\SurveyCompleted($surveyCompletion,user()));
        }
      }
      return $query;
    }
  }

}
