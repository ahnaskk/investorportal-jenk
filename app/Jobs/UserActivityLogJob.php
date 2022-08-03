<?php

namespace App\Jobs;

use App\Providers\UserActivityLogServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserActivityLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 25;

    public $timeout = 24 * 60 * 60;

    public $type;
    public $details;
    public $action;
    public $userId;
    public $objectId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId, string $type, string $action, int $objectId = 0, array $details = [])
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->action = $action;
        $this->details = $details;
        $this->objectId = $objectId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        UserActivityLogServiceProvider::saveActivityLog($this->userId, $this->type, $this->action, $this->objectId, $this->details);
    }
}
