<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB;

class AdminDashboardCopyController extends Controller
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
 		// Statistics by interviews
		if ($q->dashboard_type == 'interviews') {
			$countStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id '.$whereS.$this->basicFilters(false,'interviews.created_at','users.nationality').' LIMIT 1) as count_staff');
			$countLanguages = DB::raw("(SELECT CONCAT('{\"arabic\":',SUM(users.question_language_arabic),',\"english\":',SUM(users.question_language_english),',\"urdu\":',SUM(users.question_language_urdu),',\"other\":',SUM(users.question_language_other),'}') FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id ".$whereS.$this->basicFilters(false,'interviews.created_at','users.nationality')." LIMIT 1) as count_languages_staff");
			$countOldStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id WHERE users.staff_is_old = 1 '.$this->basicFilters(true,'interviews.created_at','users.nationality').' LIMIT 1) as count_old_staff');
			$countAttendedStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id '.$whereS.$this->basicFilters(false,'interviews.created_at','users.nationality').' LIMIT 1) as count_attended_staff');
			$countAcceptedStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id WHERE suggested_job_id IS NOT NULL '.$this->basicFilters(true,'interviews.created_at','users.nationality').' LIMIT 1) as count_accepted_staff');
			$countSaudiStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id WHERE users.nationality = "saudi" '.$this->basicFilters(true,'interviews.created_at','users.nationality').' LIMIT 1) as count_saudi_staff');
			$countMakkahStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id WHERE users.city = "Makkah" '.$this->basicFilters(true,'interviews.created_at','users.nationality').' LIMIT 1) as count_makkah_staff');
			// Age
			$ageSelect = 'YEAR(CURRENT_TIMESTAMP) - YEAR(users.birthday) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(users.birthday, 5))';
			$countAge = DB::raw("(SELECT CONCAT('[',GROUP_CONCAT(sel_users),']') FROM (SELECT CONCAT('{\"count\":',COUNT(interviews.id),',\"age\":',".$ageSelect.",'}') as sel_users FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id ".$whereS.$this->basicFilters(false,'interviews.created_at','users.nationality')." GROUP BY ".$ageSelect.") x) as count_age_staff");

			// Goal vs Actual Recuirtment Chart data
			// JSON_OBJECT("goal",jobs.goal_count,"actual",(SELECT COUNT(id) as count_actual FROM users WHERE job_id = jobs.id '.$this->basicFilters(true).' LIMIT 1) as actual_count),"job",jobs.name)
			$countRecuirtment = DB::raw("(SELECT CONCAT('[',GROUP_CONCAT(sel_jobs),']') FROM (SELECT CONCAT('{\"group\":',CONCAT('\"',job_group,'\"'),',\"name\":',CONCAT('\"',name,'\"'),',\"goal\":',goal_count,',\"actual\":',(SELECT COUNT(interviews.id) as count_actual FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id WHERE users.job_id = jobs.id ".$this->basicFilters(true,'interviews.created_at','users.nationality')." LIMIT 1),'}') as sel_jobs FROM jobs) x) as count_recuirtment");
		}elseif($q->dashboard_type == 'contracts'){
			$countAll = DB::raw('(SELECT COUNT(id) FROM users WHERE staff_is_has_contract = 1 LIMIT 1) as count_all');
			$countSigned = DB::raw('(SELECT COUNT(id) FROM users WHERE staff_is_has_contract_signed = 1 LIMIT 1) as count_signed');
			$count = DB::table('users')->select($countAll,$countSigned)->first();
			$Jobs = \App\Job::select('id',DB::raw('job_group as `group`'),'name',DB::raw('(SELECT COUNT(id) FROM users WHERE job_id = jobs.id AND staff_is_has_contract = 1) as goal'),DB::raw('(SELECT COUNT(id) FROM users WHERE job_id = jobs.id AND staff_is_has_contract_signed = 1) as actual'))->whereNotNull('job_group')->get();
			$count_all = (int) $count->count_all;
			$count_signed = (int) $count->count_signed;
			$Stats = [
				'count_all' => $count_all,
				'count_signed' => $count_signed,
				'count_not_signed' => $count_all-$count_signed,
				'count_recuirtment' => $Jobs
			];

			return response()->json($Stats);
		}elseif($q->dashboard_type == 'training'){
			// NOTE: Training statistics currently not dynamic

			$Jobs = \App\Job::select('id',DB::raw('job_group as `group`'),'name',DB::raw('tmp_training_goal as goal'),DB::raw('tmp_training_actual as actual'))->where('tmp_training_goal','>',0)->get();
			$Stats = [
				'count_all' => 12980,
				'count_attendance' => 9658,
				'count_practical' => 9395,
				'count_recuirtment' => $Jobs
			];

			return response()->json($Stats);
		}elseif($q->dashboard_type == 'practical-training'){
			// NOTE: Training statistics currently not dynamic

			$Jobs = \App\Job::select('id',DB::raw('job_group as `group`'),'name',DB::raw('tmp_practical_training_goal as goal'),DB::raw('tmp_practical_training_actual as actual'))->where('tmp_practical_training_goal','>',0)->get();
			$Stats = [
				'count_all' => 9547,
				'count_attendance' => 8819,
				'count_absence' => 728,
				'count_recuirtment' => $Jobs
			];

			return response()->json($Stats);
		}elseif($q->dashboard_type == 'workshifts'){
			if ($q->day) {
				$day = $q->day;
			}else {
				$current_hijri_day = \GeniusTS\HijriDate\Hijri::convertToHijri(date('Y-m-d'))->format('d-m');
				$conf_hijri_workshifts = config('app.hijri_workshifts');
				$day = (isset($conf_hijri_workshifts[$current_hijri_day])) ? $conf_hijri_workshifts[$current_hijri_day] : 1;
			}
			$station = ($q->station) ? $q->station : '';
			$station_shift = ($q->station_shift) ? $q->station_shift : '';
			$day_attended_station_field = 'day_'.$day.'_attended_station';
			$day_attended_checkin_field = 'day_'.$day.'_attended_checkin';
			$day_attended_checkout_field = 'day_'.$day.'_attended_checkout';
			$day_station_field = 'day_'.$day.'_station';
			$day_time_field = 'day_'.$day.'_time';

			$getIncorrectAttendance = \App\WorkShift::selectRaw($day_attended_station_field.' as station,COUNT(id) as total')->whereNotNull($day_attended_checkin_field)->whereRaw($day_attended_station_field.' != '.$day_station_field)->groupBy($day_attended_station_field);
			$getAttendance = \App\WorkShift::selectRaw($day_attended_station_field.' as station,COUNT(id) as total')->whereNotNull($day_attended_checkin_field)->groupBy($day_attended_station_field);
			$getCheckin = \App\WorkShift::selectRaw($day_attended_station_field.' as station,COUNT(id) as total')->whereNotNull($day_attended_checkin_field)->groupBy($day_attended_station_field);
			$getCheckout = \App\WorkShift::selectRaw($day_attended_station_field.' as station,COUNT(id) as total')->whereNotNull($day_attended_checkout_field)->groupBy($day_attended_station_field);
			$getTotal = \App\WorkShift::selectRaw($day_attended_station_field.' as station,COUNT(id) as total')->groupBy($day_attended_station_field);
			if ($station) {
				$getTotal = $getTotal->where($day_attended_station_field,$station);
				$getCheckout = $getCheckout->where($day_attended_station_field,$station);
				$getCheckin = $getCheckin->where($day_attended_station_field,$station);
				$getAttendance = $getAttendance->where($day_attended_station_field,$station);
				$getIncorrectAttendance = $getIncorrectAttendance->where($day_attended_station_field,$station);

				if ($station_shift) {
					$getTotal = $getTotal->where($day_time_field,$station_shift);
					$getCheckout = $getCheckout->where($day_time_field,$station_shift);
					$getCheckin = $getCheckin->where($day_time_field,$station_shift);
					$getAttendance = $getAttendance->where($day_time_field,$station_shift);
					$getIncorrectAttendance = $getIncorrectAttendance->where($day_time_field,$station_shift);
				}
			}
			$getIncorrectAttendance = $getIncorrectAttendance->get();
			$getTotal = $getTotal->get();
			$getCheckin = $getCheckin->get();
			$getCheckout = $getCheckout->get();
			$getAttendance = $getAttendance->get();
			// $getJobs = \App\Job::selectRaw($day_station_field.' as station,COUNT(id) as total')->leftJoin(DB::raw('work_shifts as ws'),'ws.user_id','=','users.id')->leftJoin(DB::raw('work_shifts as ws'),'ws.user_id','=','users.id')->get();

			$whereJobsStation = ($station) ? ' AND work_shifts.'.$day_station_field.' = "'.$station.'"' : '';
			$whereJobsStationShift = ($station_shift) ? ' AND work_shifts.'.$day_time_field.' = "'.$station_shift.'"' : '';
			$selJobs_1 = '(SELECT COUNT(jobs.id) FROM jobs LEFT JOIN users ON users.job_id = jobs.id LEFT JOIN work_shifts ON work_shifts.user_id = users.id WHERE '.$day_station_field.' IS NOT NULL '.$whereJobsStation.$whereJobsStationShift.' AND jobs.id = main_jobs.id) as total_staff';
			$selJobs_2 = '(SELECT COUNT(jobs.id) FROM jobs LEFT JOIN users ON users.job_id = jobs.id LEFT JOIN work_shifts ON work_shifts.user_id = users.id WHERE '.$day_attended_station_field.' IS NOT NULL '.$whereJobsStation.$whereJobsStationShift.' AND jobs.id = main_jobs.id) as total_attendance';
			$selJobs_3 = '(SELECT COUNT(jobs.id) FROM jobs LEFT JOIN users ON users.job_id = jobs.id LEFT JOIN work_shifts ON work_shifts.user_id = users.id WHERE '.$day_attended_station_field.' IS NOT NULL '.$whereJobsStation.$whereJobsStationShift.' AND '.$day_attended_checkin_field.' IS NOT NULL AND jobs.id = main_jobs.id) as total_checkin';
			$selJobs_4 = '(SELECT COUNT(jobs.id) FROM jobs LEFT JOIN users ON users.job_id = jobs.id LEFT JOIN work_shifts ON work_shifts.user_id = users.id WHERE '.$day_attended_station_field.' IS NOT NULL '.$whereJobsStation.$whereJobsStationShift.' AND '.$day_attended_checkout_field.' IS NOT NULL AND jobs.id = main_jobs.id) as total_checkout';
			$selJobs_5 = '(SELECT COUNT(jobs.id) FROM jobs LEFT JOIN users ON users.job_id = jobs.id LEFT JOIN work_shifts ON work_shifts.user_id = users.id WHERE '.$day_attended_station_field.' IS NOT NULL '.$whereJobsStation.$whereJobsStationShift.' AND '.$day_attended_station_field.' != '.$day_station_field.' AND jobs.id = main_jobs.id) as total_incorrect_attendance';
			$getJobs = DB::table('jobs as main_jobs')->selectRaw('main_jobs.name,"0" as total_absence,"0" as total_correct_attendance,'.$selJobs_1.','.$selJobs_2.','.$selJobs_3.','.$selJobs_4.','.$selJobs_5)->get()->map(function($item) {
				$item->total_absence = $item->total_staff-$item->total_attendance;
				$item->total_correct_attendance = $item->total_attendance-$item->total_incorrect_attendance;
				return $item;
			});
			$stations = [];
			$table = '<style type="text/css">table, td, th {
  border: 1px solid black;
}
td,th {
	padding: 5px 10px;
}

table {
  border-collapse: collapse;
  width: 100%;
	direction: rtl;
	font-family: tahoma;
	font-size: 14px;
}

th {
  text-align: right;
}</style><table><thead><th>المحطة</th><th>العدد الكلي</th><th>الحضور الكلي</th><th>الحضور الصحيح</th><th>الحضور المخالف</th><th>الغياب</th><th>الدخول</th><th>الخروج</th></thead><tbody>';
			$total_staff = 0;
			$total_attendance = 0;
			$total_absence = 0;
			$total_incorrect_attendance = 0;
			$total_correct_attendance = 0;
			$total_checkin = 0;
			$total_checkout = 0;


			foreach (config('app.stations') as $station) {
				$total = collect($getTotal->where('station',$station))->first();
				$attendance = collect($getAttendance)->where('station',$station)->first();
				$checkin = collect($getCheckin)->where('station',$station)->first();
				$checkout = collect($getCheckout)->where('station',$station)->first();
				$incorrect_attendance = collect($getIncorrectAttendance)->where('station',$station)->first();
				$station_total = ($total) ? $total->total : 0;
				$station_attendance = ($attendance) ? $attendance->total : 0;
				$station_incorrect_attendance = ($incorrect_attendance) ? $incorrect_attendance->total : 0;
				$station_correct_attendance = ($attendance && $incorrect_attendance) ? ((int) $attendance->total)-((int) $incorrect_attendance->total) : (($attendance) ? $attendance->total : 0);
				$absence = ($total && $attendance) ? ((int) $total->total)-((int) $attendance->total) : (($total) ? $total->total : 0);
				$station_checkin = ($checkin) ? ((int) $checkin->total) : 0;
				$station_checkout = ($checkout) ? ((int) $checkout->total) : 0;
				$station_data = [
					'station' => $station,
					'total' => ((int) $station_total),
					'attendance' => ((int) $station_attendance),
					'incorrect_attendance' => ((int) $station_incorrect_attendance),
					'correct_attendance' => ((int) $station_correct_attendance),
					'absence' => ((int) $absence),
					'checkin' => ((int) $station_checkin),
					'checkout' => ((int) $station_checkout)
				];
				$total_staff += $station_total;
				$total_attendance += $station_attendance;
				$total_absence += $absence;
				$total_incorrect_attendance += $station_incorrect_attendance;
				$total_correct_attendance += $station_correct_attendance;
				$total_checkin += $station_checkin;
				$total_checkout += $station_checkout;
				$stations[] = $station_data;
				$table .= '<tr><td>'.$station.'</td><td>'.$station_data['total'].'</td><td>'.$station_data['attendance'].'</td><td>'.$station_data['correct_attendance'].'</td><td>'.$station_data['incorrect_attendance'].'</td><td>'.$station_data['absence'].'</td><td>'.$station_data['checkin'].'</td><td>'.$station_data['checkout'].'</td></tr>';
			}
			$table .= '</tbody></table>';
			if ($q->table) {
				return $table;
			}else {
				$Result = [
					'jobs' => $getJobs,
					'stations' => $stations,
					'total_staff' => $total_staff,
					'total_attendance' => $total_attendance,
					'total_absence' => $total_absence,
					'total_incorrect_attendance' => $total_incorrect_attendance,
					'total_correct_attendance' => $total_correct_attendance,
					'total_checkin' => $total_checkin,
					'total_checkout' => $total_checkout
				];
				return response()->json($Result);
			}


		}else {
			// Statistics by default
			$countStaff = DB::raw('(SELECT COUNT(id) FROM users '.$whereS.$this->basicFilters().' LIMIT 1) as count_staff');
			$countLanguages = DB::raw("(SELECT CONCAT('{\"arabic\":',SUM(question_language_arabic),',\"english\":',SUM(question_language_english),',\"urdu\":',SUM(question_language_urdu),',\"other\":',SUM(question_language_other),'}') FROM users ".$whereS.$this->basicFilters()." LIMIT 1) as count_languages_staff");
			$countOldStaff = DB::raw('(SELECT COUNT(id) FROM users WHERE staff_is_old = 1 '.$this->basicFilters(true).' LIMIT 1) as count_old_staff');
			$countAttendedStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id '.$whereS.$this->basicFilters(false,'interviews.created_at','users.nationality').' LIMIT 1) as count_attended_staff');
			$countAcceptedStaff = DB::raw('(SELECT COUNT(interviews.id) FROM interviews LEFT JOIN users ON users.id = interviews.candidate_id WHERE suggested_job_id IS NOT NULL '.$this->basicFilters(true,'interviews.created_at','users.nationality').' LIMIT 1) as count_accepted_staff');
			$countSaudiStaff = DB::raw('(SELECT COUNT(id) FROM users WHERE nationality = "saudi" '.$this->basicFilters(true).' LIMIT 1) as count_saudi_staff');
			$countMakkahStaff = DB::raw('(SELECT COUNT(id) FROM users WHERE city = "Makkah" '.$this->basicFilters(true).' LIMIT 1) as count_makkah_staff');
			// Age
			$ageSelect = 'YEAR(CURRENT_TIMESTAMP) - YEAR(birthday) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthday, 5))';
			$countAge = DB::raw("(SELECT CONCAT('[',GROUP_CONCAT(sel_users),']') FROM (SELECT CONCAT('{\"count\":',COUNT(id),',\"age\":',".$ageSelect.",'}') as sel_users FROM users ".$whereS.$this->basicFilters()." GROUP BY ".$ageSelect.") x) as count_age_staff");

			// Goal vs Actual Recuirtment Chart data
			// JSON_OBJECT("goal",jobs.goal_count,"actual",(SELECT COUNT(id) as count_actual FROM users WHERE job_id = jobs.id '.$this->basicFilters(true).' LIMIT 1) as actual_count),"job",jobs.name)
			$countRecuirtment = DB::raw("(SELECT CONCAT('[',GROUP_CONCAT(sel_jobs),']') FROM (SELECT CONCAT('{\"group\":',CONCAT('\"',job_group,'\"'),',\"name\":',CONCAT('\"',name,'\"'),',\"goal\":',goal_count,',\"actual\":',(SELECT COUNT(id) as count_actual FROM users WHERE job_id = jobs.id ".$this->basicFilters(true)." LIMIT 1),'}') as sel_jobs FROM jobs WHERE jobs.goal_count != 0) x) as count_recuirtment");

		}

		$Statistics = DB::table('users')->select($countStaff,$countOldStaff,$countAttendedStaff,$countAcceptedStaff,$countSaudiStaff,$countAge,$countRecuirtment,$countLanguages,$countMakkahStaff)->first();

		// Prepare json
		$count_makkah = (int) $Statistics->count_makkah_staff;
		$count_saudi = (int) $Statistics->count_saudi_staff;
		$count_old = (int) $Statistics->count_old_staff;

		$count_attended = (int) $Statistics->count_attended_staff;
		$count_accepted = (int) $Statistics->count_accepted_staff;
		$Stats = [
			'count_all' => $Statistics->count_staff,
			'old_new' => [
				'old' => $count_old,
				'new' => $Statistics->count_staff-$count_old
			],
			'saudi_foreign' => [
				'saudi' => $count_saudi,
				'foreign' => ($Statistics->count_staff) ? $Statistics->count_staff-$count_saudi : 0
			],
			'city' => [
				'makkah' => $count_makkah,
				'other' => ($Statistics->count_staff) ? $Statistics->count_staff-$count_makkah : 0
			],
			'attended_accepted' => [
				'accepted' => $count_accepted,
				'attended' => $count_attended,
				'rejected' => ($count_attended - $count_accepted)
			],
			'count_recuirtment' => json_decode($Statistics->count_recuirtment),
			'languages' => json_decode($Statistics->count_languages_staff),
			'ages' => json_decode($Statistics->count_age_staff)
		];
		return response()->json($Stats);
	}

	/**
	* Function to set filters to query
	* @return string
	**/
	private function basicFilters($has_first_and = false,$date_col = 'created_at',$nationality_col = 'nationality'){
		$r = '';
		if (request()->start_date) {
			$r .= ' AND DATE('.$date_col.') >= "'.request()->start_date.'"';
		}
		if (request()->end_date) {
			$r .= ' AND DATE('.$date_col.') <= "'.request()->end_date.'"';
		}
		if (request()->nationality && request()->nationality != 'all') {
			$r .= ' AND '.$nationality_col.' '.((request()->nationality == 'saudi') ? '=' : '!=' ).' "saudi"';
		}
		if (!$has_first_and) {
			$prefix = ' AND';
			$r = substr($r, strlen($prefix));
		}
		return $r;
	}

}
