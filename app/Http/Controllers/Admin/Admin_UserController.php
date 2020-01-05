<?php

	namespace App\Http\Controllers\Admin;

	use Illuminate\Http\Request;
	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\Validation\Rule;
	use App\User;
	use DB;

	class Admin_UserController extends Controller
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
			$User = User::where('id',$id)->authorized()->firstOrFail();
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
			$admin_groups = [
				'requester','community_representative','reviewer','approval','printer'
			];
			if (user()->is_supervisor) {
				$admin_groups[] = 'supervisor';
				$admin_groups[] = 'manage_users';
			}

			$validation = [
				'name' => 'required',
				'sa_id' => 'required',
				'phone' => 'required',
				'admin_group' => ['required',Rule::in($admin_groups)],
	      'email' => 'required|email|max:255'
			];
			if (!$id) {
				$validation['password'] = 'required';
			}
			$validator = Validator::make($q->all(), $validation);

			if($validator->fails()) {
				return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
			}

			if ($id) {
				$User = User::where('id',$id)->authorized()->firstOrFail();
			}else {
				$User = new User;
			}

			$checkUser = User::where('email',$q->email)->select('id','email')->first();
			if ($checkUser && $checkUser->id != $id) {
				if ($checkUser->email == $q->email) {
					return response()->json(['message' => 'email_already_exists']);
				}
			}
			$User->name = $q->name;
			$User->sa_id = $q->sa_id;
			$User->phone = $q->phone;
			if (user()->id != $id) {
				$User->is_admin = 1;
				$User->is_supervisor = (user()->is_supervisor && $q->admin_group == 'supervisor') ? 1 : 0;
				$User->admin_group = $q->admin_group;
			}
	    $User->email = $q->email;
			if ($q->password) {
				$User->password = bcrypt($q->password);
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
			User::where('id',$id)->authorized()->delete();
		}



	}
