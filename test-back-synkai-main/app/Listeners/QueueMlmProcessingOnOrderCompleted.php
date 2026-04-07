<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\ProcessOrderMlmAccrualsJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueMlmProcessingOnOrderCompleted implements ShouldQueue
{
    public string $queue;

    public function __construct()
    {
        $this->queue = config('mlm.queues.binary', 'default');
    }

    public function handle(OrderCompleted $event): void
    {
        ProcessOrderMlmAccrualsJob::dispatch($event->order->id);
    }
}
