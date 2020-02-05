<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB,Validator;
use Illuminate\Validation\Rule;
use \GeniusTS\HijriDate\Hijri as Hijri;
use App\Survey,App\SurveyAnswer,App\SurveyAnswerValue,App\SurveySection,App\SurveyQuestion,App\SurveyQuestionOption;
class AdminSurveyController extends Controller
{

	/**
	* Save the main informations of survey such as title and so on
	*
	* @param mixed $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function saveInfo($id = null,Request $q)
	{
		$validator = Validator::make($q->all(), [
			'title_ar' => 'required',
			'title_en' => 'required',
			'title_fr' => 'required',
			'user_role_id' => 'required'
		]);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()],403);
		}

		if (!$id) {
			$Survey = new Survey;
			$Survey->created_by_id = user()->id;
		}else {
			$Survey = Survey::where('id',$id)->first();
			if (!$Survey) {
				return response()->json(['message' => 'survey_not_found'],404);
			}
		}

		$Survey->title_ar = $q->title_ar;
		$Survey->title_en = $q->title_en;
		$Survey->title_fr = $q->title_fr;
		$Survey->user_role_id = $q->user_role_id;
		$Survey->save();

		return response()->json($Survey);
	}

	/**
	* Show survey
	*
	* @param integer $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function getShow($id,Request $q)
	{
		$Survey = Survey::where('id',$id)->with(['MainSections' => function($Section){
			return $Section->orderBy('ordering');
		}])->first();
		if (!$Survey) {
			return response()->json(['message' => 'survey_not_found'],404);
		}
		return response()->json($Survey);
	}


	/**
	* Delete survey
	*
	* @param mixed $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function deleteSurvey($id,Request $q)
	{

		Survey::where('id',$id)->delete();

		return response()->json(['message' => 'success']);
	}

	/**
	* Activate/Deactive survey
	*
	* @param mixed $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function Activation($id,Request $q)
	{
		$validator = Validator::make($q->all(), [
			'status' => 'required|in:1,0'
		]);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()],403);
		}

		Survey::where('id',$id)->update(['is_active' => $q->status]);

		return response()->json(['message' => 'success']);
	}

	/**
	* Get Main Section Details
	*
	* @param integer $id section id
	* @param object $q
	*
	* @return array
	*/
	public function getMainSectionDetails($id,Request $q)
	{
		return response()->json($this->prepareSubSections($id));
	}


	/**
	* Prepare sub sections of parent section
	*
	*/
	public function prepareSubSections($parent_id,$view = 'show',$with_user_answer = false)
	{
		$getSubSections = SurveySection::where('parent_id',$parent_id);
		if ($view == 'export') {
			$getSubSections = $getSubSections->select('id','parent_id',DB::raw('title_ar as title'));
		}
		$getSubSections = $getSubSections->with(['Questions' => function($Question) use($view,$with_user_answer){
			if ($view == 'export') {
				$Question = $Question->select('id','survey_section_id','type','is_has_notes',DB::raw('title_ar as title'));
			}
			$Question = $Question->with(['Options' => function($Options) use ($view){
				if ($view == 'export') {
					return $Options->select('id','survey_question_id',DB::raw('title_ar as title'));
				}
				return $Options;
			}]);
			if ($with_user_answer) {
				$Question = $Question->with(['LastAnswerValue' => function($LastAnswerValue) use($with_user_answer){
					return $LastAnswerValue->where('user_id',$with_user_answer);
				}]);
			}
			return $Question;
		}]);


		$getSubSections = $getSubSections->get();

		if(!$getSubSections->count()){
			return [];
		}else {
			$allSubSections = [];
			foreach($getSubSections as $subSection){
				$sectionDetails = [
					'details' => $subSection,
					'sections' => $this->prepareSubSections($subSection->id)
				];
				$allSubSections[] = $sectionDetails;
			}
			return $allSubSections;
		}
	}


	/**
	* Save section
	*
	* @param mixed $id section id
	* @param object $q
	*
	* @return array
	*/
	public function saveSection($id = null,Request $q)
	{
		$validator = Validator::make($q->all(), [
			'title_ar' => 'required',
			'title_en' => 'required',
			'title_fr' => 'required',
			'is_required' => 'integer',
			'is_apply_percentage' => 'integer',
			'survey_id' => 'integer',
			'parent_id' => 'integer'
		]);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()],403);
		}

		if (!$id) {
			$Survey = new SurveySection;
			$Survey->survey_id = $q->survey_id;
			$Survey->parent_id = $q->parent_id;
		}else {
			$Survey = SurveySection::where('id',$id)->first();
			if (!$Survey) {
				return response()->json(['message' => 'survey_section_not_found'],404);
			}
		}

		$Survey->title_ar = $q->title_ar;
		$Survey->title_en = $q->title_en;
		$Survey->title_fr = $q->title_fr;
		$Survey->is_required = ($q->is_required) ? $q->is_required : 0;
		$Survey->is_apply_percentage = ($q->is_apply_percentage) ? $q->is_apply_percentage : 0;
		$Survey->save();

		return response()->json($Survey);
	}

	/**
	* Delete section
	*
	* @param mixed $id section id
	* @param object $q
	*
	* @return array
	*/
	public function deleteSection($id,Request $q)
	{
		SurveySection::where('id',$id)->delete();
		return response()->json(['message' => 'success']);
	}

	/**
	* Save question
	*
	* @param mixed $id survey question id
	* @param object $q
	*
	* @return array
	*/
	public function saveQuestion($id = null,Request $q)
	{
		$validator = Validator::make($q->all(), [
			'title_ar' => 'required',
			'title_en' => 'required',
			'title_fr' => 'required',
			'type' => 'required',
			'is_has_notes' => 'integer',
			'survey_id' => 'integer',
			'survey_section_id' => 'integer',
			'options' => 'array',
			'deleted_options' => 'array'
		]);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()],403);
		}

		if (!$id) {
			$Question = new SurveyQuestion;
			$Question->survey_id = $q->survey_id;
			$Question->survey_section_id = $q->survey_section_id;
		}else {
			$Question = SurveyQuestion::where('id',$id)->first();
			if (!$Question) {
				return response()->json(['message' => 'survey_question_not_found'],404);
			}
		}

		$Question->title_ar = $q->title_ar;
		$Question->title_en = $q->title_en;
		$Question->title_fr = $q->title_fr;
		$Question->type = $q->type;
		if (in_array($q->type,['time','timerange'])) {
			$Question->type_options = $q->type_options;
		}
		$Question->is_has_notes = ($q->is_has_notes) ? $q->is_has_notes : 0;
		$Question->save();

		if (in_array($q->type,['select','select_with_other','checkbox'])) {
			if (count($q->options)) {
				foreach($q->options as $option){
					if (isset($option['title_ar'])) {
						if (isset($option['id']) && $option['id']) {
							$QuestionOption = SurveyQuestionOption::where('id',$option['id'])->first();
						}else {
							$QuestionOption = new SurveyQuestionOption;
							$QuestionOption->survey_id = $Question->survey_id;
							$QuestionOption->survey_question_id = $Question->id;
						}
						if ($QuestionOption) {
							$QuestionOption->title_ar = $option['title_ar'];
							$QuestionOption->title_en = $option['title_en'];
							$QuestionOption->title_fr = $option['title_fr'];
							$QuestionOption->save();
						}
					}
				}
			}
		}

		if (is_array($q->deleted_options) && count($q->deleted_options)) {
			$deleteQuestionOption = SurveyQuestionOption::whereIn('id',$q->deleted_options)->where('survey_question_id',$Question->id)->delete();
		}

		$Question = SurveyQuestion::where('id',$Question->id)->with('Options')->first();
		return response()->json($Question);
	}

	/**
	* Delete question
	*
	* @param mixed $id question id
	* @param object $q
	*
	* @return array
	*/
	public function deleteQuestion($id,Request $q)
	{
		SurveyQuestion::where('id',$id)->delete();
		SurveyQuestionOption::where('survey_question_id',$id)->delete();
		return response()->json(['message' => 'success']);
	}

	/**
	* Export survey answers
	*
	* @param integer $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function exportSurveyAnswers($id,Request $q)
	{

		$getUsers = \App\User::whereHas('SurveyAnswer',function($SurveyAnswer) use($id){
			return $SurveyAnswer->where('survey_id',$id);
		})->select('id','username','name')->get();



		$Survey = Survey::where('id',$id)->select('id',DB::raw('title_ar as title'))->with(['MainSections' => function($MainSection){
			return $MainSection->select('id','survey_id','parent_id',DB::raw('title_ar as title'))->orderBy('ordering');
		}])->first();

		if (!$Survey) {
			return response()->json(['message' => 'survey_not_found'],404);
		}

		$Heading = [];
		$Answers = [];

		foreach($Survey->MainSections as $Section){
			$pushHeading = [
				'details' => $Section,
				'sections' => $this->prepareSubSections($Section->id,'export',77)
			];
			$Heading[] = $pushHeading;
		}

		foreach($getUsers as $User){
			$Answer = [];
			foreach($Survey->MainSections as $Section){
				$pushAnswer = [
					'details' => $Section,
					'sections' => $this->prepareSubSections($Section->id,'export',77)
				];
				$Answer[] = $pushAnswer;
			}
			$pushUser = $User;
			$pushUser->Answer = $Answer;
			$Answers[] = $pushUser;
		}

		return response()->json($Answers);
		// return \Excel::download(new \App\Exports\SurveyAnswersExport($Survey,$Heading,$Answers), $Survey->title.'.xlsx');
	}



}
