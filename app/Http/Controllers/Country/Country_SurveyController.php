<?php

namespace App\Http\Controllers\Country;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB,Validator;
use Illuminate\Validation\Rule;
use \GeniusTS\HijriDate\Hijri as Hijri;
use App\Survey,App\SurveyAnswer,App\SurveyAnswerValue,App\SurveySection,App\SurveyQuestion,App\SurveyQuestionOption;
class Country_SurveyController extends Controller
{


	/**
	* Get list of Surveys
	*
	* @param integer $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function getList($id = null,Request $q)
	{
		$Surveys = Survey::onlyActive()->select('id',DB::raw('title_'.app()->getLocale().' as title'),'created_at')->with('LastAnswer')->calculateCompletion(user()->id);

		/* Check if completed */
		if ($q->completion && $q->completion != 'all') {
			$count_mark = ($q->completion == 'completed') ? '==' : '!=';
			$Surveys = $Surveys->having('questions_count', $count_mark, 'completed_questions_count');
		}

		$Surveys = $Surveys->get();
		return response()->json($Surveys);
	}


	/**
	* Show survey
	*
	* @param integer $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function getShow($id = null,Request $q)
	{
		$Survey = Survey::where('id',$id)->select('id',DB::raw('title_'.app()->getLocale().' as title'),'created_at')->with(['MainSections' => function($Section){
			return $Section->select('id','survey_id','is_required','is_apply_percentage',DB::raw('title_'.app()->getLocale().' as title'))->orderBy('ordering');
		}])->calculateCompletion(user()->id)->first();
		if (!$Survey) {
			return response()->json(['message' => 'survey_not_found'],404);
		}

		$this->requiredQuestions = collect([]);
		$this->prepareRequiredQuestionsIds(0,0);

		return response()->json(['survey' => $Survey,'required_questions' => $this->requiredQuestions]);
	}

	/**
	* When we had to build a validation system for the main sections it is need to get all the required questions
	* once the survey opens so to let us check unfilled questions when click on save survey button
	*
	* @param integer $survey_id
	* @param integer $parent_id
	* @param object $q
	*
	* @return array
	*/
	public function prepareRequiredQuestionsIds($main_section_id,$parent_id)
	{
		$getSubSections = SurveySection::where('parent_id',$parent_id)->select('id')->with(['Questions' => function($Question) use($main_section_id){

			return $Question->select('id',DB::raw('('.(($main_section_id == 0) ? 'survey_section_id' : '"'.$main_section_id.'"').') as main_section_id'),'survey_section_id');
		}]);
		if ($parent_id == 0) {
			$getSubSections = $getSubSections->where('is_required',1);
		}
		$getSubSections = $getSubSections->get();

		if($getSubSections->count()){
			foreach($getSubSections as $subSection){
				$this->requiredQuestions = $this->requiredQuestions->merge($subSection->Questions);
				$main_section_id = ($parent_id == 0) ? $subSection->id : $main_section_id;
				$this->prepareRequiredQuestionsIds($main_section_id,$subSection->id);
			}
		}
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
	public function prepareSubSections($parent_id)
	{
		$getSubSections = SurveySection::where('parent_id',$parent_id)->select('id','survey_id','is_required','is_apply_percentage',DB::raw('title_'.app()->getLocale().' as title'))->with(['Questions' => function($Question){
			return $Question->select('id','survey_id','type','type_options','survey_section_id',DB::raw('title_'.app()->getLocale().' as title'),'is_has_notes')->with(['Options' => function($Section){
				return $Section->select('id','survey_question_id',DB::raw('title_'.app()->getLocale().' as title'));
			}])->with('LastAnswerValue');
		}])->get();

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
	* Save answer
	*
	* @param integer $id survey id
	* @param object $q
	*
	* @return array
	*/
	public function postAnswer($id = null,Request $q)
	{

		$validator = Validator::make($q->all(), [
			'section_id' => 'required|integer',
			'answers' => 'required|array'
		]);

		if($validator->fails()) {
			return response()->json(['message' => 'invalid_fields', 'errors' => $validator->messages()]);
		}

		$Survey = Survey::where('id',$id)->select('id')->first();
		if (!$Survey) {
			return response()->json(['message' => 'survey_not_found'],404);
		}


		// Insert new answers
		if (count($q->answers)) {
			/* Start clone the previous survey */
			$prevSurveyAnswer = SurveyAnswer::where('survey_id',$id)->with(['Values' => function($Values) use($q){
				return $Values->whereHas('Question',function($Question) use($q){
					return $Question->where('survey_section_id','!=',$q->section_id);
				});
			}])->orderBy('id','DESC')->first();

			$SurveyAnswer = new SurveyAnswer;
			$SurveyAnswer->survey_id = $id;
			$SurveyAnswer->user_id = user()->id;
			$SurveyAnswer->save();
			if ($prevSurveyAnswer) {
				foreach($prevSurveyAnswer->Values as $Value){
					$SurveyAnswerValue = new SurveyAnswerValue;
					$SurveyAnswerValue->survey_id = $id;
					$SurveyAnswerValue->survey_answer_id = $SurveyAnswer->id;
					$SurveyAnswerValue->survey_question_id = $Value->survey_question_id;
					$SurveyAnswerValue->survey_question_option_id = $Value->survey_question_option_id;
					$SurveyAnswerValue->user_id = user()->id;
					$SurveyAnswerValue->value = $Value->value;
					$SurveyAnswerValue->notes = $Value->notes;
					$SurveyAnswerValue->save();
				}
			}
			/* End clone  */

			foreach($q->answers as $question_id => $question_value){
				$is_can_save = true;
				$Question = SurveyQuestion::where([['survey_id',$id],['id',$question_id]])->firstOrFail();
				$SurveyAnswerValue = SurveyAnswerValue::where([['survey_id',$id],['survey_question_id',$question_id],['survey_answer_id',$SurveyAnswer->id]])->first();
				if(isset($question_value['value']) && !is_null($question_value['value'])){
					if(!$SurveyAnswerValue){
						$SurveyAnswerValue = new SurveyAnswerValue;
						$SurveyAnswerValue->survey_id = $id;
						$SurveyAnswerValue->user_id = user()->id;
						$SurveyAnswerValue->survey_answer_id = $SurveyAnswer->id;
						$SurveyAnswerValue->survey_question_id = $question_id;
					}
					// If question type is multiple choices field then validate it
					if (in_array($Question->type,['select','radio']) || ($Question->type == 'select_with_other' && !(isset($question_value['other_value']) || $question_value['value'] == 'other'))) {
						$checkQuestionOption = SurveyQuestionOption::where([['survey_id',$id],['id',$question_value['value']]])->count();
						if (!$checkQuestionOption) {
							abort(404);
						}else {
							$SurveyAnswerValue->value = $question_value['value'];
							$SurveyAnswerValue->survey_question_option_id = $question_value['value'];
						}
					}else {
						$SurveyAnswerValue->survey_question_option_id = 0;
						switch ($Question->type) {
							case 'checkbox':
								$checkboxValues = [];
								if (is_array($question_value['value'])) {
									foreach ($question_value['value'] as $value_id => $value_bool) {
										if ($value_bool) {
											$checkQuestionOption = SurveyQuestionOption::where([['survey_id',$id],['id',$value_id]])->count();
											if ($checkQuestionOption) {
												$checkboxValues[] = $value_id;
											}
										}
									}
								}
								$SurveyAnswerValue->value = (count($checkboxValues)) ? join(',',$checkboxValues) : null;
							break;
							case 'date_hijri': case 'date':
							if(!isset($question_value['value']['day']) || !isset($question_value['value']['month'])){
								$is_can_save = false;
							}
							$curYear = ($Question->type == 'date_hijri') ? \GeniusTS\HijriDate\Date::today()->format('Y') : date('Y');
							$SurveyAnswerValue->value = $curYear.'-'.$question_value['value']['month'].'-'.$question_value['value']['day'];
							break;
							case 'timerange':
							$SurveyAnswerValue->value = $question_value['value'].'-'.$question_value['to_value'];
							break;
							case 'select_with_other':
							$SurveyAnswerValue->value = ($question_value['value'] == 'other') ? $question_value['other_value'] : $question_value['value'];
							break;
							default:
							$SurveyAnswerValue->value = $question_value['value'];
							break;
						}

					}
					if ($Question->is_has_notes && isset($question_value['notes'])) {
						$SurveyAnswerValue->notes = $question_value['notes'];
					}
					if ($is_can_save) {
						$SurveyAnswerValue->save();
					}
				}
			}
		}


		return response()->json(['message' => 'success']);
	}


}
