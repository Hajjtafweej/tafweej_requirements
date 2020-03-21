<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ListOfGeographicalScope extends Model
{
  protected $table = 'list_of_geographical_scopes';
  protected $guarded = ['id'];

  /**
  * Requirements
  *
  */
  public function Requirements(){
    return $this->hasMany('App\Requirement','geographical_scope_id','id');
  }
}
