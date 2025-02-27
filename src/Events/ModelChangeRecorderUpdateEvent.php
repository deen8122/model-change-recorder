<?php

namespace Deen812\ModelChangeRecorder\Events;

use Illuminate\Database\Eloquent\Model;

class ModelChangeRecorderUpdateEvent
{
    public function __construct(Model $model)
    {
        (new ModelChangeRecorderEvents())->updating($model);
    }
}
