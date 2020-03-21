<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ListOfSubject extends Model
{
  protected $table = 'list_of_subjects';
  protected $guarded = ['id'];

  /**
  * Requirements
  *
  */
  public function Requirements(){
    return $this->hasMany('App\Requirement','subject_id','id');
  }
}
