<?php

namespace Deen812\ModelChangeRecorder\Traits;

use Deen812\ModelChangeRecorder\Models\ModelChangeRecorder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait ModelChangeRecorderTrait
{
    public function modelChangeRecords(): HasMany
    {
        return $this->HasMany(ModelChangeRecorder::class, 'model_id', 'STRING('.$this->primaryKey.')');
    }

    /**
     * Последнее изменение
     */
    public function lastChanged(): HasOne
    {
        return $this->hasOne(ModelChangeRecorder::class, 'model_id', $this->primaryKey)->orderBy(
            'created_at',
            'desc'
        );
    }
}