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
				return $this->Participants($q);
			break;
			case 'requirements':
				return $this->Requirements($q);
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

		$getResults = \App\Participant::selectRaw('participants.id,participants.name,IFNULL((SELECT COUNT(id) FROM requirement_participants WHERE participant_id = participants.id GROUP BY requirement_participants.participant_id LIMIT 1),0) as requirements_count');

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

		$getResults = \App\Requirement::selectRaw('requirements.id,subject.name as subject_name,sub_subject.name as sub_subject_name,level.name as level_name,geographical_scope.name as geographical_scope_name,requirements.business_scope,requirements.requirements,holy_place.name as holy_place_name,IFNULL((SELECT COUNT(id) FROM requirement_participants WHERE requirement_id = requirements.id GROUP BY requirement_participants.requirement_id LIMIT 1),0) as participants_count');
		$getResults = $getResults->leftJoin('list_of_holy_places as holy_place','requirements.holy_place_id','=','holy_place.id');
		$getResults = $getResults->leftJoin('list_of_subjects as subject','requirements.subject_id','=','subject.id');
		$getResults = $getResults->leftJoin('list_of_sub_subjects as sub_subject','requirements.sub_subject_id','=','sub_subject.id');
		$getResults = $getResults->leftJoin('list_of_levels as level','requirements.level_id','=','level.id');
		$getResults = $getResults->leftJoin('list_of_geographical_scopes as geographical_scope','requirements.geographical_scope_id','=','geographical_scope.id');

		// Holy place filter
		if ($q->holy_place_id && $q->holy_place_id != 'all') {
			$getResults = $getResults->where(DB::raw('requirements.holy_place_id'),$q->holy_place_id);
		}

		// Subject filter
		if ($q->subject_id && $q->subject_id != 'all') {
			$getResults = $getResults->where(DB::raw('requirements.subject_id'),$q->subject_id);
		}

		// Sub Subject filter
		if ($q->sub_subject_id && $q->sub_subject_id != 'all') {
			$getResults = $getResults->where(DB::raw('requirements.sub_subject_id'),$q->sub_subject_id);
		}

		// Geographical Scope filter
		if ($q->geographical_scope_id && $q->geographical_scope_id != 'all') {
			$getResults = $getResults->where(DB::raw('requirements.geographical_scope_id'),$q->geographical_scope_id);
		}

		// Level filter
		if ($q->level_id && $q->level_id != 'all') {
			$getResults = $getResults->where(DB::raw('requirements.level_id'),$q->level_id);
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
			return datatables()->of($getResults)->filterColumn('holy_place_name', function($query, $keyword) use($q) {
				$query->whereRaw('holy_place.name like ?', ["%{$keyword}%"]);
			})->filterColumn('subject_name', function($query, $keyword) use($q) {
				$query->whereRaw('subject.name like ?', ["%{$keyword}%"]);
			})->filterColumn('sub_subject_name', function($query, $keyword) use($q) {
				$query->whereRaw('sub_subject.name like ?', ["%{$keyword}%"]);
			})->filterColumn('level_name', function($query, $keyword) use($q) {
				$query->whereRaw('level.name like ?', ["%{$keyword}%"]);
			})->filterColumn('geographical_scope_name', function($query, $keyword) use($q) {
				$query->whereRaw('geographical_scope.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

}
