<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ExampleListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ExampleEvent $event): void
    {
        Log::debug("The user {$event->username} just {$event->action}.");
    }
}
