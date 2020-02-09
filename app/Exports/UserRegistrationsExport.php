<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class UserRegistrationsExport implements FromView,ShouldAutoSize,WithEvents
{

  /**
  * Assign model data from controller
  *
  * @param $Model Eloquent
  * @param array $Columns
  */
  public function __construct($Model)
  {
      $this->Model = $Model;
  }

  public function view() : View
  {
    return view('app.exports.user-registrations', [
      'UserRegistrations' => $this->Model
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
