<?php

namespace DreamHack\SDK\Mail;

use Illuminate\Bus\Queueable;

class MailJob
{
    use Queueable;
    /**
     * Allow jobs to fail as much as they want for 2 hours
     * Due to mandrill api beeing so unstable
     */
    public function retryUntil()
    {
        return now()->addHours(2);
    }
}
