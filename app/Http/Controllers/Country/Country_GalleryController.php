<?php

namespace App\Http\Controllers\Country;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB,Validator;
use Illuminate\Validation\Rule;
use App\Gallery,App\Upload;
class Country_GalleryController extends Controller
{


	/**
	* Get list of Gallery
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
		$Gallery = Gallery::where('user_id',user()->id)->get();

		// Skip
		if ($q->load_more_type == 'pagination') {
			// Pagination
			$Gallery = $Gallery->paginate(30);
		}else {
			$take = ($q->take) ? $q->take : 3;
			$Gallery = $Gallery->take($take);
			if ($q->skip) {
				$Gallery = $Gallery->skip($q->skip);
			}
			$Gallery = $Gallery->get();
		}

		return response()->json($Gallery);
	}

	/**
	* Get recent uploads of gallery
	*
	* @param object $q
	*/
	public function getRecentUploads(Request $q)
	{
		$Uploads = Upload::where('user_id',user()->id)->with(['Gallery' => function($Gallery){
			return $Gallery->select('id',DB::raw('title_ar as title'),'created_at');
		}])->whereModule('gallery')->orderBy('created_at','DESC')->take(20)->get();
		return response()->json($Uploads);
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
