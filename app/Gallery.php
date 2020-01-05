<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
  protected $table = 'gallery';
  protected $guarded = ['id'];


  /**
  * Uploads
  *
  */
  public function Uploads(){
    return $this->hasMany('App\Upload','id','module_id')->where('module','gallery');
  }

}
