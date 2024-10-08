<?php

namespace App\Jobs;

use App\Mail\SendOtpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOtpNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $otp ;
    public $user ;

    public function __construct($otp, $user)
    {
        $this->otp = $otp ;
        $this->user = $user ;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //info($this->user->email) ;
        Mail::send(new SendOtpMail($this->otp, $this->user)) ;
    }
}
