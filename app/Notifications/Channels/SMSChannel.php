<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Unifonic;
class SMSChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toSMS($notifiable);
        Unifonic::sendBulk([$data['phone']],$data['message'],date('Y-m-d H:i:s'));
    }
}
