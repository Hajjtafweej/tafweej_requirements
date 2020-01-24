<?php

	namespace App\Http\Controllers\Admin;

	use Illuminate\Http\Request;

	use App\Http\Requests;
	use App\Http\Controllers\Controller;
	use Flow\Config as FlowConfig;
	use Flow\Request as FlowRequest;
	use Flow\ConfigInterface;
	use Flow\RequestInterface;
	use Image,Excel;
	use App\User;
	use App\Http\Controllers\AdminController;

	class AdminFlowUploaderController extends Controller
	{
		//
		public $file_path;
		public $full_path;
		private $allowed_mimes = ['image/png','image/jpeg','image/gif','image/bmp'];

		public function upload($folder,$is_image,$width = 0,$height = 0,$thumb_width = 0,$thumb_height = 0){
			if (domain() == 'localhost') {
				$this->uploads_path = public_path('uploads');
				$this->assets_path = public_path('assets');
			}else {
				$this->uploads_path = base_path('uploads');
				$this->assets_path = base_path('assets');
			}
			// config flow uploader
			$config = new FlowConfig();
			$config->setTempDir(storage_path('flowjs_tmp'));
			$config->setDeleteChunksOnSave(true);
			$file = new \Flow\File($config);
			$request = new FlowRequest();
			$totalSize = $request->getTotalSize();
			// 100 MB
			if ($totalSize && $totalSize > (104857600))
			{
				return response()->make('',400);
			}
			$uploadFile = $request->getFile();
			// check mime type
			if ($is_image && !in_array($uploadFile['type'],$this->allowed_mimes)) {
				return response()->make('',400);
			}
			if ($file->validateChunk()) {
				$file->saveChunk();
				} else {
				return response()->make('',204);
			}
			// generate name
			$p = pathinfo($uploadFile['name']);
			$image_hash = str_slug(str_replace('.'.$p['extension'],'',$p['basename'])).'-'.rand(11111111,99999999).'-'.date('ymd');
			$this->file_path = $image_hash.'.'.$p['extension'];
			$this->full_path =  $folder.'/' . $this->file_path;
			$this->full_path = $this->uploads_path.'/'. $this->full_path;

			// save file
			if ($file->validateFile() && $file->save($this->full_path))
			{
				$file->deleteChunks();
				// Is image
				if($is_image){
					/* Resize Original Image */
					if($width || $height){
						$Image = Image::make($this->full_path);
						// Insert water mark
						if($width && $Image->width() > $width && !$height){
							$Image->resize($width,null,function($constraint) {
								$constraint->aspectRatio();
							});
						}elseif(!$width && $height && $Image->height() > $height){
							$Image->resize(null,$height,function($constraint) {
								$constraint->aspectRatio();
							});
						}elseif($width && $height) {
							$Image->resize($width,$height);
						}
						$Image->save($this->full_path);
					}
					/* Make Thumbnail */
					if($thumb_width || $thumb_height){
						$ThumbImage = Image::make($this->full_path);
						$thumb_path = $this->uploads_path.'/'.$folder.'/thumb_'.$image_hash.'.'.$p['extension'];

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
						}
					}
				}
				return response()->make($this->file_path, 200);
			}

		}


		public function Import(){
			return $this->upload('imports',false);
		}

		public function StartImport(Request $q){
			if (domain() == 'localhost') {
				$this->uploads_path = public_path('uploads');
			}else {
				$this->uploads_path = base_path('uploads');
			}
			$this->file = '';
			$this->is_file = false;
			$file_path = $this->uploads_path.'/imports/'.$q->file;
			switch ($q->type) {
				case 'interview-candidates':
					if(auth()->user()->can('interviews_manager')){
						Excel::import(new \App\Imports\InterviewCandidatesImport, $file_path);
					}
				break;
				case 'training-participants':
					if(auth()->user()->can('training_manager')){
						// Get training groups count
						$Training = \App\Training::select('name')->where('id',$q->id)->firstOrFail();
						if ($Training) {
							$TrainingGroupsCount = \App\TrainingGroup::selectRaw('COUNT(id) as count_groups')->where('training_id',$q->id)->groupBy('training_id')->first();
							$group_no = $TrainingGroupsCount->count_groups+1;
							$TrainingGroup = new \App\TrainingGroup;
							$TrainingGroup->training_id = $q->id;
							$TrainingGroup->no = $group_no;
							$TrainingGroup->name = $Training->name.' '.$group_no;
							$TrainingGroup->save();
							$Import = (new \App\Imports\TrainingParticipantsImport)->toArray($file_path);
							$Participants = [];
							if (isset($Import[0])) {
								foreach($Import[0] as $Item) {
									if (isset($Item['sa_id'])) {
										$saId = substr($Item['sa_id'],0,10);
										$User = \App\User::where('sa_id',$Item['sa_id'])->select('id')->first();
										if ($User) {
											$Participants[] = ['participant_id' => $User->id,'training_id' => $q->id,'training_group_id' => $TrainingGroup->id];
										}
									}
								}
							}
							$TrainingGroupParticipant = \App\TrainingGroupParticipant::insert($Participants);
						}
					}
				break;
				case 'catering':
					if(auth()->user()->can('staff')){

						$Import = (new \App\Imports\CateringImport)->toArray($file_path);
						if (isset($Import[0])) {
							foreach($Import[0] as $Item) {
								if (isset($Item['sa_id'])) {
									$User = \App\User::where('sa_id',$Item['sa_id'])->select('id')->first();
									if ($User) {
										$Catering = \App\Catering::where('user_id',$User->id)->first();
										if (!$Catering) {
											$Catering = new \App\Catering;
											$Catering->user_id = $User->id;
										}
										$Catering->date = $Item['date'];
										$Catering->hour = $Item['hour'];
										$Catering->building = $Item['building'];
										$Catering->hall = $Item['hall'];
										$Catering->group = $Item['group'];
										$Catering->table = $Item['table'];
										$Catering->save();
									}
								}
							}
						}

					}
				break;
			}
			if ($this->is_file) {
				return response()->json(['is_file' => $this->is_file,'file' => $this->file]);
			}else {
				return response()->json(['status' => 'success']);
			}
		}

		/**
		* Upload file to share it with another users
		*/
		public function postShared(Request $q){
			return $this->upload('shared',false);
		}


	}
