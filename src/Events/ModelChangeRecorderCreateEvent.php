<?php

namespace Deen812\ModelChangeRecorder\Events;

use Illuminate\Database\Eloquent\Model;

class ModelChangeRecorderCreateEvent
{
    public function __construct(Model $model)
    {
        (new ModelChangeRecorderEvents())->created($model);
    }
}
