<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class UsersExport implements FromView,ShouldAutoSize
{

  /**
  * Assign model data from controller
  *
  * @param $Model Eloquent
  * @param array $Columns
  */
  public function __construct($Model,$Columns = [])
  {
    config(['app.debug' => true]);
      $this->Model = $Model;
      $this->Columns = $Columns;
  }

  public function view() : View
  {
    return view('app.exports.staff', [
      'Staff' => $this->Model,
      'Columns' => $this->Columns
    ]);
  }
}
