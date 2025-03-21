<?php

namespace Deen812\ModelChangeRecorder\Jobs;


use Deen812\ModelChangeRecorder\Services\ModelChangeRecorderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ModelChangeRecorderJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    protected  $model;
    protected  $action;
    protected  $diff;
    protected  $userId;
    protected  $callBy;

    public function __construct(Model $model, string|int|null $userId, array|bool $diff, string $action, $callBy)
    {
        $this->model = serialize($model);
        $this->action = $action;
        $this->diff = serialize($diff);
        $this->userId = $userId;
        $this->callBy = $callBy;
    }

    /**
     * Получить уникальный идентификатор задания.
     */
    public function uniqueId(): string
    {
        if($this->action == 'Create' || $this->action == 'Delete'){
            return (string)rand(1,10000);
        }
        $model =   unserialize($this->model);
        return $this->diff;
    }

    public function handle(): void
    {
        $this->model = unserialize($this->model);
        $this->diff = unserialize($this->diff);

        // Проверяем что мы запустили первый раз
        if ($this->attempts() > 1) {
            return;
        }

        (new ModelChangeRecorderService())->recordModel(
            $this->model,
            $this->userId,
            $this->diff,
            $this->action,
            $this->callBy
        );
    }
}
