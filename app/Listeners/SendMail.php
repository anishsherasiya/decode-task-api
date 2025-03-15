<?php

namespace App\Listeners;

use App\Events\CSVProccessingDone;
use App\Mail\CSVMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        Mail::to('decodeadmin@gmail.com')->send(new CSVMail());

    }

    /**
     * Handle the event.
     */
    public function handle(CSVProccessingDone $event): void
    {
        //
    }
}
