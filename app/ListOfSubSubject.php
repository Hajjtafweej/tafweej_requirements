<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ListOfSubSubject extends Model
{
  protected $table = 'list_of_sub_subjects';
  protected $guarded = ['id'];

  /**
  * Requirements
  *
  */
  public function Requirements(){
    return $this->hasMany('App\Requirement','sub_subject_id','id');
  }
}
