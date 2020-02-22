<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;
use DB;

class AdminDashboardController extends Controller
{

	/**
	* Get
	*/
	public function getStatistics(Request $q)
	{
		$validation = [
			'user_role_id' => 'required|integer'
		];
		$validator = Validator::make($q->all(), $validation);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
		}

		$Response = [];
		
		/*
		Because the survey_id filter is an important part in the dashboard
		so if survey_id not exists in request we need to set default one depends on user_role_id
		*/
		$getDefaultSurvey = \App\Survey::select('id',DB::raw('title_ar as title'));
		if(!$q->survey_id){
			$getDefaultSurvey = $getDefaultSurvey->where('user_role_id',$q->user_role_id)->orderBy('created_at','DESC')->first();
			$survey_id = $getDefaultSurvey->id;
		}else {
			$getDefaultSurvey = $getDefaultSurvey->where('id',$q->survey_id)->first();
			$survey_id = $q->survey_id;
		}
		$Response['survey'] = $getDefaultSurvey;


		DB::statement(DB::raw('SET SESSION group_concat_max_len = 10000000;'));
		$Statistics = [];
		// Where
		$this->start_date = $q->start_date;
		$this->end_date = $q->end_date;
		$whereS = ($this->basicFilters()) ? 'WHERE ' : '';

		// Statistics by default
		$countAllUsers = DB::raw('(SELECT COUNT(id) FROM users '.$whereS.$this->basicFilters(false).' LIMIT 1) as count_all_users');
		$countCompletedSurveys = DB::raw('(SELECT COUNT(survey_logs.user_id) FROM survey_logs WHERE survey_logs.survey_id = '.$survey_id.' AND survey_logs.completed_at IS NOT NULL AND survey_logs.user_id IN(SELECT users_of_role.id FROM users as users_of_role WHERE users_of_role.user_role_id = '.$q->user_role_id.') GROUP BY survey_logs.survey_id) as count_completed_surveys');
		$countStartedSurveys = DB::raw('(SELECT COUNT(survey_logs.user_id) FROM survey_logs WHERE survey_logs.survey_id = '.$survey_id.' AND survey_logs.started_at IS NOT NULL AND survey_logs.user_id IN(SELECT users_of_role.id FROM users as users_of_role WHERE users_of_role.user_role_id = '.$q->user_role_id.') GROUP BY survey_logs.survey_id) as count_started_surveys');
		$countViewedSurveys = DB::raw('(SELECT COUNT(survey_logs.user_id) FROM survey_logs WHERE survey_logs.survey_id = '.$survey_id.' AND survey_logs.user_id IN(SELECT users_of_role.id FROM users as users_of_role WHERE users_of_role.user_role_id = '.$q->user_role_id.') GROUP BY survey_logs.survey_id) as count_viewed_surveys');

		$Statistics = DB::table('surveys')->select($countAllUsers,$countCompletedSurveys,$countStartedSurveys,$countViewedSurveys)->first();

		// Prepare json
		$count_all_users = (int) $Statistics->count_all_users;
		$count_completed_surveys = (int) $Statistics->count_completed_surveys;
		$count_started_surveys = (int) $Statistics->count_started_surveys;
		$count_not_started_surveys = (int) $Statistics->count_completed_surveys;
		$count_viewed_surveys = (int) $Statistics->count_viewed_surveys;

		$TopUsersInSurveyCompletion = User::whereRaw('users.user_role_id = ?',[$q->user_role_id])->selectRaw('users.id,users.name,users.username,users.email,completion_rate');
		$TopUsersInSurveyCompletion = $TopUsersInSurveyCompletion->leftJoin(DB::raw('(SELECT id,ROUND(AVG(completion_rate),2) as completion_rate,user_id,survey_id FROM survey_logs WHERE survey_logs.survey_id = '.$survey_id.' GROUP BY user_id) as users_survey_logs'),'users_survey_logs.user_id','=','users.id');
		$TopUsersInSurveyCompletion = $TopUsersInSurveyCompletion->whereRaw('users_survey_logs.id IS NOT NULL')->where(DB::raw('users_survey_logs.survey_id'),$survey_id)->where(DB::raw('users_survey_logs.completion_rate'),'>',0)->orderBy('completion_rate','DESC')->with(['SurveyLog' => function($SurveyLog){
			return $SurveyLog->with(['Survey' => function($Survey){
				return $Survey->select('id',DB::raw('title_ar as title'));
			}]);
		}])->take(7)->get();

		$LastAnswers = \App\SurveyLog::take(5)->with(['User' => function($User){
			return $User->select('id','username','name');
		},'Survey' => function($Survey){
			return $Survey->select('id',DB::raw('title_ar as title'));
		}])->whereHas('User',function($User){
			return $User->where('user_role_id',request()->user_role_id);
		})->where('survey_id',$survey_id)->orderBy(DB::raw('last_answer_at'),'DESC')->onlyStarted()->get();

		$count_not_started = $count_all_users-$count_started_surveys;
		$Response = array_merge($Response,[
			'users_count' => [
				'all' => $count_all_users,
				'completed' => $count_completed_surveys,
				'completed_rate' => (($count_completed_surveys) ? round(($count_completed_surveys/$count_all_users) * 100,2) : 0),
				'started' => $count_started_surveys,
				'started_rate' => (($count_started_surveys) ? round(($count_started_surveys/$count_all_users) * 100,2) : 0),
				'not_started' => $count_not_started,
				'not_started_rate' => (($count_not_started) ? round(($count_not_started/$count_all_users) * 100,2) : 0),
				'viewed' => $count_viewed_surveys,
				'viewed_rate' => (($count_viewed_surveys) ?  round(($count_viewed_surveys/$count_all_users) * 100,2) : 0),
			],
			'top_users_survey_completion' => $TopUsersInSurveyCompletion,
			'last_answers' => $LastAnswers
		]);

		return response()->json($Response);
	}

	/**
	* Function to set filters to query
	* @return string
	**/
	private function basicFilters($has_first_and = false,$user_role_col = 'user_role_id',$date_col = 'created_at'){
		$r = '';
		if (request()->user_role_id) {
			$r .= ' AND '.$user_role_col.' = '.request()->user_role_id;
		}
		if (request()->start_date) {
			$r .= ' AND DATE('.$date_col.') >= "'.request()->start_date.'"';
		}
		if (request()->end_date) {
			$r .= ' AND DATE('.$date_col.') <= "'.request()->end_date.'"';
		}
		if (!$has_first_and) {
			$prefix = ' AND';
			$r = substr($r, strlen($prefix));
		}
		return $r;
	}

}
