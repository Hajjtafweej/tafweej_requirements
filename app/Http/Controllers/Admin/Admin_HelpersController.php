<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Admin_HelpersController extends Controller
{


	/**
	* Get list data
	* @param string $type
	* @return string
	**/
	public function getList($type,Request $q){
		if (in_array($type,['town-lists'])) {
			$List = [];
			switch ($type) {
				case 'town-lists':
					$List = [
						'villages' => \App\Village::where('town_id',$q->town_id)->select('id','name')->orderBy('name')->get(),
						'buildings' => \App\Building::where('town_id',$q->town_id)->select('id','code')->orderBy('name')->get()
					];
					break;
			}
			return response()->json($List);
		}else {
			abort(403);
		}
	}


}
