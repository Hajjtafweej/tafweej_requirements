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
			case 'surveys':
				if(!user()->can('admin_manage_surveys')){
					abort(403);
				}
				return $this->Surveys($q);
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
				case 'cards':
					$Model = $Model->get();
					$filename = ($q->status == 0) ? 'Interviews-Candidates.xlsx' : 'Interviews.xlsx';
					return Excel::download(new \App\Exports\InterviewsExport($Model,$q->status), $filename);
				break;
			}
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

		// Status
		if ($q->status != 'all') {
			$getResults = $getResults->where(DB::raw('surveys.status'),$q->status);
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
	* Users
	*/
	public function Users($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\User::selectRaw('users.id,users.name,users.username,users.email,IF(users.is_supervisor = 1 OR users.is_admin = 1,"مدير",user_role.name) as user_role_name,users.is_supervisor,users.created_at');
		$getResults = $getResults->leftJoin('user_roles as user_role','users.user_role_id','=','user_role.id');
		// Status filter
		$date_field = 'created_at';
		// Date filter
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
		// Status filter
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

}
