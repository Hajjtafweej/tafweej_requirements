<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Presentation extends Model
{
  protected $table = 'presentations';
  protected $guarded = ['id'];


  /**
  * Uploads
  *
  */
  public function Uploads(){
    return $this->hasMany('App\Upload','module_id','id')->where('module','presentations');
  }

}
