<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SurveyAnswersExport implements FromView,ShouldAutoSize
{

  /**
  * Assign model data from controller
  *
  * @param $Model Eloquent
  * @param $Heading the heading of excel file
  * @param $Answers the answers for each users
  * @param array $Columns
  */
  public function __construct($Survey,$Heading,$Answers)
  {
      $this->Survey = $Survey;

      $this->Answers = [];
      foreach($Answers as $Answer){
        $pushAnswer = $Answer;
        $pushAnswer->Answer = $this->prepareItems($pushAnswer->Answer);
        $this->Answers[] = $pushAnswer;
      }

      $this->Heading = [];
      foreach($Heading as $HeadingItem){
        $HeadingItem = $this->prepareItems($HeadingItem);
        $this->Heading[] = $HeadingItem;
      }
  }

  private function prepareItems($Item,$Title){
    // $Result = [];
    // if ($Title) {
    //   $Title .= ' - '.$Item->details->title;
    // }else {
    //   $Title = $Item->details->title;
    // }
    // foreach($Item->Sections as $ItemSection){
    //   $Title = $this->prepareItems($ItemSection);
    // }
    // return $Title;
  }

  public function view() : View
  {
    return view('app.exports.survey-answers', [
      'Survey' => $this->Survey,
      'Heading' => $this->Heading,
      'Answers' => $this->Answers
    ]);
  }
}
