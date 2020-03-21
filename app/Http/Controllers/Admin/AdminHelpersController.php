<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminHelpersController extends Controller
{


	/**
	* Get main lists when the application initilize
	* @param string $type
	* @return string
	**/
	public function getMainLists(Request $q){
		return response()->json([
			'holy_places' => \App\ListOfHolyPlace::select('id','name')->get(),
			'subjects' => \App\ListOfSubject::select('id','name')->get(),
			'sub_subjects' => \App\ListOfSubSubject::select('id','name')->get(),
			'levels' => \App\ListOfLevel::select('id','name')->get(),
			'geographical_scopes' => \App\ListOfGeographicalScope::select('id','name')->get()
		]);
	}

	/**
	* Get list data
	* @param string $type
	* @return string
	**/
	public function getList($type,Request $q){
		if (in_array($type,['requirement-lists'])) {
			$List = [];
			switch ($type) {
				case 'requirement-lists':
					$List = [
						'participants' => \App\Participant::get()
					];
				break;
			}
			return response()->json($List);
		}else {
			abort(403);
		}
	}


}
