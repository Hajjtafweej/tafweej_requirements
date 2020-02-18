<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SurveyCompleted extends Mailable
{
    use Queueable, SerializesModels;
    public $User;
    public $Survey;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($Survey,$User)
    {
        $this->Survey = $Survey;
        $this->User = $User;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('قام المستخدم '.$this->User->name.' بإكمال الإجابة على '.$this->Survey->title_ar)->markdown('app.emails.notifications.survey-completed',['User' => $this->User,'Survey' => $this->Survey]);
    }
}
