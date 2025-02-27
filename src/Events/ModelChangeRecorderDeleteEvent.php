<?php

namespace Deen812\ModelChangeRecorder\Events;

use Illuminate\Database\Eloquent\Model;

class ModelChangeRecorderDeleteEvent
{
    public function __construct(Model $model)
    {
        (new ModelChangeRecorderEvents())->deleting($model);
    }
}
