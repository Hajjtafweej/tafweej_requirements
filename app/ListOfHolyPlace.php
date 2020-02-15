<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ListOfHolyPlace extends Model
{
  protected $table = 'list_of_holy_places';
  protected $guarded = ['id'];

  /**
  * Requirements
  *
  */
  public function Requirements(){
    return $this->hasMany('App\Requirement','holy_place_id','id');
  }
}
