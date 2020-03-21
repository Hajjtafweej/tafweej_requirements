<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Requirement extends Model
{
  protected $guarded = ['id'];
  
  /**
  * Participants
  *
  */
  public function Participants(){
    return $this->hasMany('App\RequirementParticipant');
  }

  /**
  * Subject
  *
  */
  public function Subject(){
    return $this->belongsTo('App\ListOfSubject','subject_id','id');
  }

  /**
  * Sub Subject
  *
  */
  public function SubSubject(){
    return $this->belongsTo('App\ListOfSubSubject','sub_subject_id','id');
  }

  /**
  * Level
  *
  */
  public function Level(){
    return $this->belongsTo('App\ListOfLevel','level_id','id');
  }

  /**
  * Geographical Scope 
  *
  */
  public function GeographicalScope(){
    return $this->belongsTo('App\ListOfGeographicalScope','geographical_scope_id','id');
  }
}
