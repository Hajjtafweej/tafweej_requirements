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
			'users_roles' => \App\UserRole::select('id','name')->get()
		]);
	}

	/**
	* Get list data
	* @param string $type
	* @return string
	**/
	public function getList($type,Request $q){
		if (in_array($type,['requirement-lists','surveys','users'])) {
			$List = [];
			switch ($type) {
				case 'requirement-lists':
					$List = [
						'participants' => \App\Participant::get(),
						'holy_places' => \App\ListOfHolyPlace::get()
					];
				break;
				case 'users':
					if($q->q){
						$List = \App\User::select('id','name','username');
							$List = $List->where('name','LIKE','%'.$q->q.'%')->orWhere('username','LIKE','%'.$q->q.'%');
							$List = $List->take(5)->orderBy('created_at','DESC')->get();
					}else {
						$List = [];
					}
				break;
				case 'surveys':
					$q->validate([
						'user_role_id' => 'integer'
					]);
					
					$List = \App\Survey::select('id',\DB::raw('title_ar as title'));
					if($q->q){
						$List = $List->where('title_ar','LIKE','%'.$q->q.'%');
					}
					if($q->user_role_id){
						$List = $List->where('user_role_id',$q->user_role_id);
					}
					$List = $List->take(5)->orderBy('created_at','DESC')->get();
				break;
			}
			return response()->json($List);
		}else {
			abort(403);
		}
	}


}
