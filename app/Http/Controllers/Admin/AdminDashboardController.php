<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB;

class AdminDashboardController extends Controller
{

	/**
	* Get
	*/
	public function getStatistics(Request $q)
	{

		DB::statement(DB::raw('SET SESSION group_concat_max_len = 10000000;'));
		$Statistics = [];
		// Where
		$this->start_date = $q->start_date;
		$this->end_date = $q->end_date;
		$whereS = ($this->basicFilters()) ? 'WHERE ' : '';

		// Statistics by default
		$countSurveys = DB::raw('(SELECT COUNT(id) FROM surveys '.$whereS.$this->basicFilters().' LIMIT 1) as count_surveys');
		$countCompletedSurveys = DB::raw('(SELECT COUNT(surveys.id) FROM surveys WHERE surveys.id IN(SELECT survey_logs.survey_id FROM survey_logs WHERE survey_logs.survey_id = surveys.id AND survey_logs.completed_at IS NOT NULL GROUP BY survey_logs.survey_id)) as count_completed_surveys');
		$countStartedSurveys = DB::raw('(SELECT COUNT(surveys.id) FROM surveys WHERE surveys.id IN(SELECT survey_logs.survey_id FROM survey_logs WHERE survey_logs.survey_id = surveys.id AND survey_logs.started_at IS NOT NULL GROUP BY survey_logs.user_id)) as count_started_surveys');
		$countViewedSurveys = DB::raw('(SELECT COUNT(surveys.id) FROM surveys WHERE surveys.id IN(SELECT survey_logs.survey_id FROM survey_logs WHERE survey_logs.survey_id = surveys.id GROUP BY survey_logs.user_id)) as count_viewed_surveys');

		$Statistics = DB::table('surveys')->select($countSurveys,$countCompletedSurveys,$countStartedSurveys,$countViewedSurveys)->first();

		// Prepare json
		$count_surveys = (int) $Statistics->count_surveys;
		$count_completed_surveys = (int) $Statistics->count_completed_surveys;
		$count_started_surveys = (int) $Statistics->count_started_surveys;
		$count_not_started_surveys = (int) $Statistics->count_completed_surveys;
		$count_viewed_surveys = (int) $Statistics->count_viewed_surveys;

		$TopUsersInSurveyCompletion = User::whereRaw('users.user_role_id = ?',[$q->user_role_id])->selectRaw('users.id,users.name,users.username,users.email,completion_rate');
		$TopUsersInSurveyCompletion = $TopUsersInSurveyCompletion->leftJoin(DB::raw('(SELECT id,AVG(completion_rate) as completion_rate,user_id FROM survey_logs GROUP BY user_id) as users_survey_logs'),'users_survey_logs.user_id','=','users.id');
		$TopUsersInSurveyCompletion = $TopUsersInSurveyCompletion->whereRaw('users_survey_logs.id IS NOT NULL')->where(DB::raw('users_survey_logs.completion_rate'),'>',0)->orderBy('completion_rate','DESC')->with(['SurveyLogs' => function($SurveyLogs){
			return $SurveyLogs->with(['Survey' => function($Survey){
				return $Survey->select('id',DB::raw('title_ar as title'));
			}])->take(3);
		}])->take(5)->get();

		$Stats = [
			'surveys_count' => [
				'all' => $count_surveys,
				'completed' => $count_completed_surveys,
				'completed_rate' => round(($count_completed_surveys/$count_surveys) * 100,2),
				'started' => $count_started_surveys,
				'started_rate' => round(($count_started_surveys/$count_surveys) * 100,2),
				'not_started' => ($count_surveys-$count_started_surveys),
				'not_started_rate' => round((($count_surveys-$count_started_surveys)/$count_surveys) * 100,2),
				'viewed' => $count_viewed_surveys,
				'viewed_rate' => round(($count_viewed_surveys/$count_surveys) * 100,2),
			],
			'top_users_survey_completion' => $TopUsersInSurveyCompletion
		];

		return response()->json($Stats);
	}

	/**
	* Function to set filters to query
	* @return string
	**/
	private function basicFilters($has_first_and = false,$date_col = 'created_at'){
		$r = '';
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
