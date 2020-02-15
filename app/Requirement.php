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
}
