<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class RequirementParticipant extends Model
{
  protected $guarded = ['id'];

  /**
  * Participant
  *
  */
  public function Participant(){
    return $this->belongsTo('App\Participant');
  }
}
