<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB,Excel;

class AdminDatatableController extends Controller
{

	/**
	* We use this parameter to separate the process
	* from execute data to javascript datatable plugin or just export it to an excel sheet
	* @var bool
	*/
	private $is_export = false;

	/**
	* Fill the current module from route parameter, its accept sub module such as "training-group" or main module such as "training"
	* @var string
	*/
	private $currentModule;

	/**
	* Set the current main module
	* @var string
	*/
	private $module;

	/**
	* Set the current main module id
	* @var integer
	*/
	private $moduleId;

	/**
	* Set the current sub module
	* @var string
	*/
	private $subModule;

	/**
	* Set the current sub module id
	* @var integer
	*/
	private $subModuleId;


	/**
	* Prepare and response module data either to excel or to datatable
	* @param string $module get the module type from route
	* @param object $q Request data
	*
	* @return mixed
	*/
	public function getModule($module,$sub_module = null,$module_id = 0,$sub_module_id = 0,Request $q)
	{
		$this->currentModule = ($sub_module) ? $sub_module : $module;
		$this->module = $module;
		$this->subModule = $sub_module;
		$this->moduleId = $module_id;
		$this->subModuleId = $sub_module_id;

		// If the current route is for export data to excel sheet
		if ($q->route()->getName() == 'export') {
			$this->is_export = true;
		}

		switch ($module) {
			case 'participants':
				if(!user()->can('admin_manage_participants')){
					abort(403);
				}
				if ($this->subModule) {
					switch ($this->subModule) {
						case 'requirements':
							return $this->Requirements($q);
							break;
					}
				}else {
					return $this->Participants($q);
				}

			break;
			case 'surveys':
				if(!user()->can('admin_manage_surveys')){
					abort(403);
				}
				if ($this->subModule) {
					switch ($this->subModule) {
						case 'answers':
							return $this->surveysAnswers($q);
							break;
					}
				}else {
					return $this->Surveys($q);
				}

			break;
			case 'users':
				if(!user()->can('admin_manage_users')){
					abort(403);
				}
				if ($this->subModule) {
					switch ($this->subModule) {
						case 'roles':
							return $this->usersRoles($q);
							break;
							case 'registrations':
								return $this->usersRegistrations($q);
								break;
					}
				}else {
					return $this->Users($q);
				}
			break;
			default:
			abort(403);
			break;
		}

	}

	/**
	* Export model data to excel sheet
	*
	* @param object $Model eloquent model data
	* @return mixed
	*/
	public function exportToExcel($Model)
	{
		$q = request();

		$Columns = explode(',',$q->columns);
		if (count($Columns)) {
			switch ($this->currentModule) {
				case 'users':
					$Model = $Model->get();
					$filename = 'المستخدمين.xlsx';
					return Excel::download(new \App\Exports\UsersExport($Model), $filename);
				break;
				case 'registrations':
					$Model = $Model->get();
					$filename = 'طلبات-التسجيل.xlsx';
					return Excel::download(new \App\Exports\UserRegistrationsExport($Model), $filename);
				break;
			}
		}
	}

	/**
	* Participants
	*/
	public function Participants($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\Participant::selectRaw('participants.id,participants.name');

		// Date filter
		$date_field = 'participants.created_at';
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}

		$getResults = $getResults->groupBy('participants.id');
		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {
			return datatables()->of($getResults)
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

	/**
	* Requirements
	*/
	public function Requirements($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\Requirement::selectRaw('requirements.id,requirements.element,requirements.field,requirements.sub_field,requirements.scope_of_work,requirements.requirements,holy_place.name as holy_place_name,(SELECT COUNT(id) FROM requirement_participants WHERE requirement_id = requirements.id LIMIT 1) as participants_count');
		$getResults = $getResults->leftJoin('list_of_holy_places as holy_place','requirements.holy_place_id','=','holy_place.id');

		// Holy place filter
		if ($q->holy_place_id) {
			$getResults = $getResults->where(DB::raw('requirements.holy_place_id'),$q->holy_place_id);
		}

		// Date filter
		$date_field = 'requirements.created_at';
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}

		$getResults = $getResults->groupBy('requirements.id');
		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {
			return datatables()->of($getResults)
			->filterColumn('holy_place_name', function($query, $keyword) use($q) {
				$query->whereRaw('holy_place.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}
	
	/**
	* Surveys
	*/
	public function Surveys($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\Survey::selectRaw('surveys.id,surveys.title_ar as title,surveys.created_at,surveys.is_active,IF(surveys.user_role_id = 0,"الكل",user_role.name) as user_role_name,created_by.name as created_by_name');
		$getResults = $getResults->leftJoin('user_roles as user_role','surveys.user_role_id','=','user_role.id');
		$getResults = $getResults->leftJoin('users as created_by','surveys.created_by_id','=','created_by.id');

		// Date filter
		$date_field = 'surveys.created_at';
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}

		$getResults = $getResults->groupBy('surveys.id');
		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {

			return datatables()->of($getResults)
			->filterColumn('title', function($query, $keyword) use($q) {
				$query->whereRaw('surveys.title_ar like ?', ["%{$keyword}%"]);
			})->filterColumn('user_role_name', function($query, $keyword) use($q) {
				$query->whereRaw('user_role.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

	/**
	* Surveys answers
	*/
	public function surveysAnswers($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\SurveyLog::selectRaw('survey_logs.id,survey_logs.survey_id,survey_logs.started_at,survey_logs.viewed_at,survey_logs.last_answer_at,survey_logs.completion_rate,survey_logs.completed_at,user.name,user.id as user_id,surveys.title_ar as survey_title');
		$getResults = $getResults->leftJoin('users as user','survey_logs.user_id','=','user.id');
		$getResults = $getResults->leftJoin('surveys','survey_logs.survey_id','=','surveys.id');

		// Status filter
		if($q->status && $q->status != 'all'){
			if ($q->status == 'completed') {
				$getResults = $getResults->onlyCompleted();
			}
		}

		// User role filter
		if($q->user_role_id){
			$getResults = $getResults->where(DB::raw('user.user_role_id'),$q->user_role_id);
		}

		// Survey filter
		if($q->survey_id){
			$getResults = $getResults->where(DB::raw('survey_logs.survey_id'),$q->survey_id);
		}

		// Date filter
		$date_field = 'survey_logs.last_answer_at';
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}

		$getResults = $getResults->onlyStarted()->groupBy('survey_logs.id');
		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {
			return datatables()->of($getResults)
			->filterColumn('survey_title', function($query, $keyword) use($q) {
				$query->whereRaw('surveys.title_ar like ?', ["%{$keyword}%"]);
			})->filterColumn('name', function($query, $keyword) use($q) {
				$query->whereRaw('user.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}


	/**
	* Users
	*/
	public function Users($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\User::selectRaw('users.id,users.name,users.username,users.email,IF(users.is_supervisor = 1 OR users.is_admin = 1,"مدير",user_role.name) as user_role_name,users.is_supervisor,users.created_at');
		$getResults = $getResults->leftJoin('user_roles as user_role','users.user_role_id','=','user_role.id');

		// Survey filter
		if($q->survey_id){
			if ($q->status && $q->status != 'all') {

			}
		}

		// User role filter
		if($q->user_role_id){
			$getResults = $getResults->where(DB::raw('users.user_role_id'),$q->user_role_id);
		}

		// Date filter
		$date_field = 'created_at';
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}
		$getResults = $getResults->groupBy('users.id');

		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {
			return datatables()->of($getResults)
			->filterColumn('user_role_name', function($query, $keyword) use($q) {
				$query->whereRaw('user_role.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

	/**
	* Users Roles
	*/
	public function usersRoles($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\UserRole::selectRaw('user_roles.id,user_roles.name,(SELECT COUNT(id) FROM users WHERE user_role_id = user_roles.id LIMIT 1) as users_count,user_roles.created_at');
		// Date filter
		$date_field = 'created_at';
		// Date filter
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}
		$getResults = $getResults->groupBy('user_roles.id');

		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {
			return datatables()->of($getResults)
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

	/**
	* Users Registrations
	*/
	public function usersRegistrations($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\ServiceApplyToPortal::selectRaw('service_apply_to_portal.id,country.name as country_name,service_apply_to_portal.delegation_name,service_apply_to_portal.phone,service_apply_to_portal.email,service_apply_to_portal.created_at');
		$getResults = $getResults->leftJoin('list_of_countries as country','service_apply_to_portal.country_id','=','country.id');
		// Status filter
		$date_field = 'service_apply_to_portal.created_at';
		// Date filter
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}
		$getResults = $getResults->groupBy('service_apply_to_portal.id');

		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {
			return datatables()->of($getResults)
			->filterColumn('country_name', function($query, $keyword) use($q) {
				$query->whereRaw('country.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}
}
