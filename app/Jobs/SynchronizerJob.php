<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $offset;
    /**
     * Create a new job instance.
     */
    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
