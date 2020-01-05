<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
  protected $table = 'uploads';
  protected $guarded = ['id'];

  /**
  * Gallery
  *
  */
  public function Gallery(){
    return $this->belongsTo('App\Gallery','module_id','id');
  }

  /**
  * Meeting
  *
  */
  public function Meeting(){
    return $this->belongsTo('App\Meeting','module_id','id');
  }
}
