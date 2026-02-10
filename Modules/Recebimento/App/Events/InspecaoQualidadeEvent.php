<?php

namespace Modules\Recebimento\App\Events;

use Illuminate\Queue\SerializesModels;

class InspecaoQualidadeEvent
{
    use SerializesModels;

    public array $data;

    /**
     * Create a new event instance.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
