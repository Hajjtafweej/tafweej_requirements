<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB,Excel;

class Admin_DatatableController extends Controller
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
			case 'residents':
				if(!user()->can('manage_resident')){
					abort(403);
				}
				return $this->Residents($q);
			break;
			case 'cards':
				if(!user()->can('view_cards')){
					abort(403);
				}
				return $this->Cards($q);
			break;
			case 'prints':
			if(!user()->can('print_card')){
				abort(403);
			}
			return $this->Prints($q);
			break;
			case 'users':
			if(!user()->can('manage_users')){
				abort(403);
			}
			return $this->Users($q);
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
	* Residents
	*/
	public function Residents($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$Residents = \App\Resident::selectRaw('residents.id as id,"resident" as module_type,residents.id as resident_id,residents.name as name,residents.sa_id as sa_id,residents.birthday_hijri as birthday_hijri,residents.created_at as created_at,
		town.name as town_name,
		building.code as building_code,
		village.name as village_name');
		$Residents = $Residents->leftJoin('list_of_towns as town','town.id','=','residents.town_id');
		$Residents = $Residents->leftJoin('list_of_buildings as building','building.id','=','residents.building_id');
		$Residents = $Residents->leftJoin('list_of_villages as village','village.id','=','residents.village_id');


		$residentJoinQuery = 'SELECT pr.id as resident_id,pr.sa_id as sa_id,town.name as town_name,building.code as building_code,village.name as village_name FROM residents pr LEFT JOIN list_of_towns as town ON town.id = pr.town_id LEFT JOIN list_of_villages as village ON village.id = pr.village_id LEFT JOIN list_of_buildings as building ON building.id = pr.building_id';
		$FamilyMembers = \App\ResidentFamilyMember::selectRaw('resident_family_members.id as id,"family_member" as module_type,parent_resident.resident_id as resident_id,resident_family_members.name as name,IFNULL(resident_family_members.sa_id,parent_resident.sa_id) as sa_id,resident_family_members.birthday_hijri as birthday_hijri,resident_family_members.created_at as created_at,
		parent_resident.town_name as town_name,
		parent_resident.building_code as building_code,
		parent_resident.village_name as village_name');
		$FamilyMembers = $FamilyMembers->leftJoin(DB::raw('('.$residentJoinQuery.') as parent_resident'),'parent_resident.resident_id','=','resident_family_members.resident_id');

		$getResults = $Residents->union($FamilyMembers);

		// Status filter
		$date_field = 'created_at';
		// Date filter
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}


		$getResults = $getResults->groupBy('id');


		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {

			$finalQuery = DB::table(DB::raw("({$getResults->toSql()}) as residents"));

			return datatables()->of($finalQuery)
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}


	/**
	* Cards
	*/
	public function Cards($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\Card::selectRaw('cards.id,cards.module,cards.no,cards.version,cards.expire_at,cards.status,cards.requested_type,cards.requested_at,
		town.name as details_town,
		IF(cards.module = "resident",resident.name,family_member.name) as details_name,
		IF(cards.module = "resident",resident.id,family_member.resident_id) as details_parent_resident_id,
		IF(cards.module = "resident",resident.sa_id,IFNULL(family_member.sa_id,parent_resident.sa_id)) as details_sa_id');
		$getResults = $getResults->leftJoin(DB::raw('residents as parent_resident'),'parent_resident.id','=','cards.parent_resident_id');
		$getResults = $getResults->leftJoin(DB::raw('residents as resident'),'resident.id','=','cards.module_id');
		$getResults = $getResults->leftJoin('list_of_towns as town','town.id','=','parent_resident.town_id');
		$getResults = $getResults->leftJoin('resident_family_members as family_member','family_member.id','=','cards.module_id');

		// Status filter
		$date_field = 'last_card.requested_at';
		// Date filter
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}
		// Status
		if ($q->status != 'all') {
			$getResults = $getResults->where(DB::raw('cards.status'),$q->status);
		}
		// Specific data for admin groups
		$getResults = $getResults->authorized();

		$getResults = $getResults->groupBy('cards.id');
		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {

			return datatables()->of($getResults)
			->filterColumn('details_name', function($query, $keyword) use($q) {
				$query->whereRaw('IF(cards.module = "resident",resident.name,family_member.name) like ?', ["%{$keyword}%"]);
			})->filterColumn('details_sa_id', function($query, $keyword) use($q) {
				$query->whereRaw('IF(cards.module = "resident",resident.sa_id,family_member.sa_id) like ?', ["%{$keyword}%"]);
			})->filterColumn('details_town', function($query, $keyword) use($q) {
				$query->whereRaw('town.name like ?', ["%{$keyword}%"]);
			})->filterColumn('details_village', function($query, $keyword) use($q) {
				$query->whereRaw('village.name like ?', ["%{$keyword}%"]);
			})
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

	/**
	* Prints
	*/
	public function Prints($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\LogPrintedCard::selectRaw('log_printed_cards.id,log_printed_cards.count,log_printed_cards.path,log_printed_cards.commitment_path,log_printed_cards.created_at,
		users.name as created_by_name');
		$getResults = $getResults->leftJoin('users','users.id','=','log_printed_cards.created_by_id');

		// Status filter
		$date_field = 'log_printed_cards.created_at';
		// Date filter
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}

		$getResults = $getResults->authorized()->groupBy('log_printed_cards.id');
		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {

			return datatables()->of($getResults)
			->filterColumn('created_by_name', function($query, $keyword) use($q) {
				$query->whereRaw('users.name like ?', ["%{$keyword}%"]);
			})->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}


	/**
	* Users
	*/
	public function Users($q)
	{

		$start_date = ($q->start_date) ? $q->start_date : false;
		$end_date = ($q->end_date) ? $q->end_date : false;

		$getResults = \App\User::selectRaw('users.id,users.name,users.email,users.sa_id,users.phone,users.is_supervisor,users.admin_group,users.created_at');

		// Status filter
		$date_field = 'created_at';
		// Date filter
		if($start_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'>=',$start_date);
		}
		if($end_date){
			$getResults = $getResults->whereDate(DB::raw($date_field),'<=',$end_date);
		}
		if($q->perm && $q->perm != 'all'){
			$getResults = $getResults->where('admin_group',$q->perm);
		}
		$getResults = $getResults->authorized()->groupBy('users.id');

		if($this->is_export){
			return $this->exportToExcel($getResults);
		}else {

			return datatables()->of($getResults)
			->addColumn('DT_RowId','{{ strtolower($id) }}')->make(true);
		}

	}

}
