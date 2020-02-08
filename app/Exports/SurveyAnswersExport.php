<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class SurveyAnswersExport implements FromView,ShouldAutoSize,WithEvents
{

  /**
  * Assign model data from controller
  *
  * @param $Model Eloquent
  * @param $Heading the heading of excel file
  * @param $Answers the answers for each users
  * @param array $Columns
  */
  public function __construct($Survey,$Heading,$usersAnswers)
  {
    $this->Survey = $Survey;

    $this->Answers = [];
    foreach($usersAnswers as $userAnswer){
      $this->currentUserAnswers = [];
      $this->prepareItems('Answers',$userAnswer->Answer,'',true);
      $userAnswer->Answer = $this->currentUserAnswers;
      $this->Answers[] = $userAnswer;
    }

    $this->Heading = [];
    $this->prepareHeadingTitles = [];
    $this->prepareItems('Heading',$Heading,'',true);
    // \Log::info($this->Answers);
  }

  /**
  * Reorder the serialization of data in Heading and Answers array to merge all items in one series of array
  *
  * @param string $type the array name to push the results inside it
  * @param array $Items the items in each level
  * @param string $Title used in heading array to naming the titles of excel columns
  * @param boolean $isFirst
  */
  private function prepareItems($Type,$Items,$Title,$isFirst){
    foreach($Items as $Item){
      $Title = '';
      if ($Type == 'Heading') {
        $pushTitle = $Item['details']->title;
        if ($Title) {
          $Title .= ' - '.$pushTitle;
        }else {
          $Title = $pushTitle;
        }
      }
      foreach($Item['details']->Questions as $Question){
        if ($Type == 'Answers') {
          $this->currentUserAnswers[] = $Question;
        }else {
          $this->Heading[] = $Title.' - '.$Question->title;
        }
      }
      $this->prepareItems($Type,$Item['sections'],$Title,false);
    }
  }

  public function view() : View
  {
    return view('app.exports.survey-answers', [
    'Survey' => $this->Survey,
    'Heading' => $this->Heading,
    'Answers' => $this->Answers
    ]);
  }

  /**
  * @return array
  */
  public function registerEvents(): array
  {
    return [
    AfterSheet::class    => function(AfterSheet $event) {
      $event->sheet->styleCells(
      'A1:'.$event->sheet->getDelegate()->getHighestColumn().'1',
      [
      'alignment' => [
      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
      ],
      'font' => [
      'bold' => true,
      'color' => ['argb' => 'FFFFFF'],
      ]
      ]
      );

      $event->sheet->getStyle('A1:'.$event->sheet->getDelegate()->getHighestColumn().'1')->getFill()
      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      ->getStartColor()->setARGB(config('app.app_settings.primary_color'));
    },
    ];
  }
}
