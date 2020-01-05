<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
  protected $table = 'meetings';
  protected $guarded = ['id'];


  /**
  * Uploads
  *
  */
  public function Uploads(){
    return $this->hasMany('App\Upload','id','module_id')->where('module','meetings');
  }

}
