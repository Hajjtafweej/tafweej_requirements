<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ListOfLevel extends Model
{
  protected $table = 'list_of_levels';
  protected $guarded = ['id'];

  /**
  * Requirements
  *
  */
  public function Requirements(){
    return $this->hasMany('App\Requirement','level_id','id');
  }
}
