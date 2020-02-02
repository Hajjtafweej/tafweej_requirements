<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Survey extends Model
{
  protected $table = 'surveys';
  protected $guarded = ['id'];

  /**
  * Sections
  *
  */
  public function Sections(){
    return $this->hasMany('App\SurveySection');
  }

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
    return $this->hasOne('App\SurveyAnswer')->where('user_id',user()->id)->orderBy('id','DESC');
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

  /**
  * Model events
  *
  */
  public static function boot() {
    parent::boot();

    static::deleting(function($Survey) {
      $Survey->Sections()->delete();
      $Survey->Questions()->delete();
    });
  }

  /**
  * Authorized
  */
  public function scopeAuthorized($query){
    if (!user()->is_admin) {
      $query = $query->whereIn(\DB::raw($this->table.'.user_role_id'),[0,user()->user_role_id]);
    }
    return $query;
  }
}
