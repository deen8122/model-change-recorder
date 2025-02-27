<?php

namespace Deen812\ModelChangeRecorder\Jobs;


use Deen812\ModelChangeRecorder\Services\ModelChangeRecorderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ModelChangeRecorderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $model;
    protected $action;
    protected $diff;
    protected $userId;
    protected $callBy;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, $userId, array|bool $diff, $action, $callBy)
    {
        $this->model = serialize($model);
        $this->action = $action;
        $this->diff = serialize($diff);
        $this->userId = $userId;
        $this->callBy = $callBy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->model = unserialize($this->model);
        $this->diff = unserialize($this->diff);

        // Проверяем что мы запустили первый раз
        if ($this->attempts() > 1) {
            return;
        }

        //if (\App::environment('prod')) {
        (new ModelChangeRecorderService())->recordModel(
            $this->model,
            $this->userId,
            $this->diff,
            $this->action,
            $this->callBy
        );
        //}
    }

}
