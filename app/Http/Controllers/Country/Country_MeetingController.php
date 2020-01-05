<?php

namespace App\Http\Controllers\Country;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB,Validator;
use Illuminate\Validation\Rule;
use \GeniusTS\HijriDate\Hijri as Hijri;
use App\Meeting,App\Upload;
class Country_MeetingController extends Controller
{


		/**
		* Get list of meetings
		*
		* @param object $q
		*/
		public function getList(Request $q)
		{
			$validator = Validator::make($q->all(), [
			'skip' => 'numeric',
			'take' => 'numeric'
			]);
			if($validator->fails()) {
				return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()],401);
			}

			$Gallery = Gallery::where('user_id',user()->id);
			$Gallery = $Gallery->take(3);
			$Gallery = $Gallery->get();
			return response()->json($Gallery);
		}


		/**
		* Show gallery item
		*
		* @param integer $id gallery item id
		* @param object $q
		*
		* @return array
		*/
		public function getShow($id = null,Request $q)
		{
			$GalleryItem = Gallery::where('id',$id)->where('user_id',user()->id)->with('Uploads')->first();
			if (!$GalleryItem) {
				return response()->json(['message' => 'gallery_item_not_found'],404);
			}

			return response()->json($GalleryItem);
		}

}
