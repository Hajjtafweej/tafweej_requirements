<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB,Validator;
use Illuminate\Validation\Rule;
use \GeniusTS\HijriDate\Hijri as Hijri;
class PortalHelpersController extends Controller
{



	/**
	* Save answer
	*
	* @param integer $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function postApplyToPortal(Request $q)
	{

		$validator = Validator::make($q->all(), [
			'delegation_name' => 'required|string',
			'email' => 'required|email',
			'phone' => 'required',
			'country_id' => 'required|integer'
		]);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
		}

		$checkCountryId = \App\ListOfCountry::where('id',$q->country_id)->select('id')->firstOrFail();
		$ServiceApplyToPortal = new \App\ServiceApplyToPortal;
		$ServiceApplyToPortal->country_id = $q->country_id;
		$ServiceApplyToPortal->delegation_name = $q->delegation_name;
		$ServiceApplyToPortal->email = $q->email;
		$ServiceApplyToPortal->phone = $q->phone;
		$ServiceApplyToPortal->save();

		return response()->json(['message' => 'success']);
	}


}
