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
  * Users
  *
  */
  public function Users(){
    return $this->hasMany('App\SurveyUser');
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
  * Question Options
  *
  */
  public function QuestionOptions(){
    return $this->hasMany('App\SurveyQuestionOption');
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
  * Survey Log
  *
  */
  public function SurveyLog(){
    return $this->hasOne('App\SurveyLog');
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
  public function scopeCalculateCompletion($query,$user_id = null)
  {
    return $query->withCount(['Questions','CompletedQuestions' => function($CompletedQuestions) use($user_id){
			return $CompletedQuestions->whereHas('LastAnswerValue',function($LastAnswerValue)  use($user_id){
				return $LastAnswerValue->where('user_id',($user_id ?? user()->id));
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
      $query = $query->where(function($where){
        return $where->whereHas('Users',function($SurveyUsers){
          return $SurveyUsers->where('user_id',user()->id);
        })->orWhere(function($orWhere){
          return $orWhere->whereDoesntHave('Users')->whereIn(\DB::raw($this->table.'.user_role_id'),[0,user()->user_role_id]);
        });
      });
    }
    return $query;
  }
}
