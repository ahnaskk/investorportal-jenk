<?php

namespace App\Jobs;

use App\Providers\PermissionLogServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PermissionLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
        public $tries = 25;

    public $timeout = 24 * 60 * 60;

    public $type;
    public $details;
    public $action;
    public $modifier;
    public $objectId;
    public $roleId;
    public $moduleId;
    public $userId;
    public function __construct(int $modifier, string $type, string $action, int $objectId = 0, array $details = [], int $roleId = 0, int $moduleId = 0, int $userId = 0)
    {
        $this->modifier = $modifier;
        $this->type = $type;
        $this->action = $action;
        $this->details = $details;
        $this->objectId = $objectId;
        $this->roleId = $roleId;
        $this->moduleId = $moduleId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        PermissionLogServiceProvider::savePermissionLog($this->modifier, $this->type, $this->action, $this->objectId, $this->details, $this->roleId, $this->moduleId, $this->userId);
    }
}
