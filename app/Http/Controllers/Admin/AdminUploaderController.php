<?php

	namespace App\Http\Controllers\Admin;

	use Illuminate\Http\Request;
	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;
	use Image;

	class AdminUploaderController extends Controller
	{
		//
		public $file_path;
		public $full_path;

		public function Upload(Request $q){
			$folder = 'images';
			if (domain() == 'localhost') {
			    $disk = 'public-uploads';
				$this->uploads_path = public_path('uploads');
			}else {
			    $disk = 'uploads';
				$this->uploads_path = base_path('uploads');
			}
			$file = $q->file;
			$fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
			$filePath = $folder.'/'.$fileName;
			Storage::disk($disk)->put($filePath, file_get_contents($file));
			$result = [
					'file_path' => $filePath,
					'path' => $fileName
			];
			// Make thumbnail

			$thumb_width = 250;
			$thumb_height = 0;
				$ThumbImage = Image::make($this->uploads_path.'/'.$filePath);
				$thumb_path = $this->uploads_path.'/'.$folder.'/thumb_'.$fileName;

				if($thumb_width && $ThumbImage->width() > $thumb_width && !$thumb_height){
					$ThumbImage->resize($thumb_width,null,function($constraint) {
						$constraint->aspectRatio();
					})->save($thumb_path);
				}elseif(!$thumb_width && $thumb_height){
					if ($ThumbImage->height() > $thumb_height) {
						$ThumbImage->resize(null,$thumb_height,function($constraint) {
							$constraint->aspectRatio();
						})->save($thumb_path);
					}else {
						$ThumbImage->save($thumb_path);
					}
				}elseif($thumb_width && $thumb_height) {
					$ThumbImage->resize($thumb_width,$thumb_height)->save($thumb_path);
				}else {
					$ThumbImage->save($thumb_path);
				}
			 return response()->json($result);
		}


	}
