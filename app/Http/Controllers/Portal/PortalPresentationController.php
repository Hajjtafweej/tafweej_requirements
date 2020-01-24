<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB,Validator;
use Illuminate\Validation\Rule;
use \GeniusTS\HijriDate\Hijri as Hijri;
use App\Presentation,App\Upload;
use Zipper;
class PortalPresentationController extends Controller
{


		/**
		* Get list of Presentations
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
			$Presentations = Presentation::select('id',DB::raw('title_'.app()->getLocale().' as title'),'created_at','is_downloaded','downloaded_at')->where('user_id',user()->id)->withCount('uploads');

			/* Check if downloaded */
			if ($q->download && $q->download != 'all') {
				$count_mark = ($q->download == 'not-downloaded') ? '==' : '!=';
				$Presentations = $Presentations->where('is_downloaded',$count_mark,0);
			}

			// Skip
			if ($q->load_more_type == 'pagination') {
				// Pagination
				$Presentations = $Presentations->paginate(50);
				return response()->json($Presentations,200,[],JSON_UNESCAPED_UNICODE);
			}else {
				$take = ($q->take) ? $q->take : 20;
				$Presentations = $Presentations->take($take);
				if ($q->skip) {
					$Presentations = $Presentations->skip($q->skip);
				}
				$Presentations = $Presentations->get();
				return response()->json($Presentations,200,[],JSON_UNESCAPED_UNICODE);
			}
		}


		/**
		* Show presentation item
		*
		* @param integer $id presentation item id
		* @param object $q
		*
		* @return array
		*/
		public function getShow($id = null,Request $q)
		{
			$PresentationItem = Presentation::where('id',$id)->where('user_id',user()->id)->with('Uploads')->first();
			if (!$PresentationItem) {
				return response()->json(['message' => 'presentation_item_not_found'],404);
			}

			return response()->json($PresentationItem);
		}

		/**
		* Download the presentation
		*
		* @param integer $id presentation item id
		* @param object $q
		*
		* @return array
		*/
		public function getDownload($id,Request $q)
		{
			$Presentation = Presentation::where('id',$id)->select('id',DB::raw('title_'.app()->getLocale().' as title'))->where('user_id',user()->id)->with('Uploads')->firstOrFail();
			if (!$Presentation->downloaded_at) {
				$Presentation->is_downloaded = 1;
				$Presentation->downloaded_at = date('Y-m-d H:i:s');
			}
			$Presentation->save();

			/* Start prepare download */
			$files = [];
			foreach($Presentation->Uploads as $Upload){
				$files[] = public_path('uploads/files/'.$Upload->path);
			}
			$fileName = user()->id.'-'.$Presentation->title.'.zip';
			Zipper::make(public_path('/uploads/archives/'.$fileName)) //file path for zip file
			->add($files)->close(); //files to be zipped

			return response()->download(public_path('/uploads/archives/'.$fileName),$fileName,[
				'Content-Type' => 'application/zip'
			]);
			/* End prepare download */
		}

}
