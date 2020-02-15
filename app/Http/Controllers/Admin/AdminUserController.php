<?php

	namespace App\Http\Controllers\Admin;

	use Illuminate\Http\Request;
	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\Validation\Rule;
	use App\User,App\UserRole;
	use DB;

	class AdminUserController extends Controller
	{


		/**
	  * Show user details
	  *
	  * @param integer $id
	  * @param Request $q
	  * @return \Illuminate\Http\JsonResponse
	  */
		public function getShow($id,Request $q)
		{
			$User = User::where('id',$id)->firstOrFail();
			return response()->json($User);
		}

		/**
	  * Save user
	  *
	  * @param mixed $id
	  * @param Request $q
	  * @return \Illuminate\Http\JsonResponse
	  */
		public function Save($id = null,Request $q)
		{

			$validation = [
				'name' => 'required',
				'username' => 'required',
				'is_supervisor' => 'integer',
				'is_admin' => 'integer',
				'user_role_id' => 'integer',
	      		'email' => 'required|email|max:255'
			];
			$validator = Validator::make($q->all(), $validation);

			if($validator->fails()) {
				return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
			}

			$checkUser = User::where('email',$q->email)->orWhere('username',$q->username)->select('id','email','username')->first();
			if ($checkUser && $checkUser->id != $id) {
				if ($checkUser->email == $q->email) {
					return response()->json(['message' => 'email_already_exists']);
				}elseif ($checkUser->username == $q->username) {
					return response()->json(['message' => 'username_already_exists']);
				}
			}

			if ($id) {
				$User = User::where('id',$id)->firstOrFail();
			}else {
				$User = new User;
			}

			$User->name = $q->name;
			$User->username = $q->username;
			$User->user_role_id = $q->user_role_id;
			if (user()->id != $id) {
				$User->is_supervisor = ($q->is_supervisor) ? 1 : 0;
				if ($q->is_admin) {
					$User->is_admin = 1;
				}
			}
	    $User->email = $q->email;
			if ($q->password) {
				$User->password = bcrypt($q->password);
			}elseif(!$id){
				$User->password = bcrypt(config('app.app_settings.default_password'));
			}
			$User->save();

			return response()->json($User->id);
		}

		/**
	  * Delete user
	  *
	  * @param integer $id
	  * @param Request $q
	  * @return \Illuminate\Http\JsonResponse
	  */
		public function Delete($id,Request $q)
		{
			if (user()->id == $id) {
				abort(403);
			}
			User::where('id',$id)->delete();
		}


		/**
	  * Show user role details
	  *
	  * @param integer $id
	  * @param Request $q
	  * @return \Illuminate\Http\JsonResponse
	  */
		public function getShowRole($id,Request $q)
		{
			$UserRole = UserRole::where('id',$id)->firstOrFail();
			return response()->json($UserRole);
		}

		/**
	  * Save user role
	  *
	  * @param mixed $id
	  * @param Request $q
	  * @return \Illuminate\Http\JsonResponse
	  */
		public function saveRole($id = null,Request $q)
		{

			$validation = [
				'name' => 'required'
			];
			$validator = Validator::make($q->all(), $validation);

			if($validator->fails()) {
				return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
			}

			if ($id) {
				$UserRole = UserRole::where('id',$id)->firstOrFail();
			}else {
				$UserRole = new UserRole;
			}

			$UserRole->name = $q->name;
			$UserRole->is_allow_survey = ($q->is_allow_survey) ? $q->is_allow_survey : 0;
			$UserRole->is_allow_gallery = ($q->is_allow_gallery) ? $q->is_allow_gallery : 0;
			$UserRole->is_allow_presentations = ($q->is_allow_presentations) ? $q->is_allow_presentations : 0;
			$UserRole->is_allow_tafweej_plans = ($q->is_allow_tafweej_plans) ? $q->is_allow_tafweej_plans : 0;
			$UserRole->is_allow_tafweej_tables = ($q->is_allow_tafweej_tables) ? $q->is_allow_tafweej_tables : 0;
			$UserRole->save();

			return response()->json($UserRole->id);
		}

		/**
	  * Delete user role
	  *
	  * @param integer $id
	  * @param Request $q
	  * @return \Illuminate\Http\JsonResponse
	  */
		public function deleteRole($id,Request $q)
		{
			$checkUsersBelongs = User::where('user_role_id',$id)->count();
			if ($checkUsersBelongs) {
				return response()->json(['message' => 'there_are_users_exist']);
			}else {
				UserRole::where('id',$id)->delete();
				\App\Survey::where('user_role_id',$id)->update(['user_role_id' => 0]);
				return response()->json(['message' => 'success']);
			}
		}

		/**
		* Delete registration request
		*
		* @param integer $id
		* @param Request $q
		* @return \Illuminate\Http\JsonResponse
		*/
		public function deleteRegistration($id,Request $q)
		{
			\App\ServiceApplyToPortal::where('id',$id)->delete();
		}

	}
