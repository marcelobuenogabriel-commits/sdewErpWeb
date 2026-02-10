<?php

namespace Modules\Recebimento\App\Listeners;

use Modules\Recebimento\App\Events\InspecaoQualidadeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InspecaoQualidadeListener
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
    public function handle(InspecaoQualidadeEvent $event): void
    {
        $inspecao = $event->data['codPin'] ?? null;

        if (is_null($inspecao)) {
            
        } 
    }
}
