<?php


namespace Deen812\ModelChangeRecorder\Services;

use Deen812\ModelChangeRecorder\Events\ModelChangeRecorderEvents;
use Illuminate\Database\Eloquent\Builder;

class ModelChangeRecorderQueryBuilderDecorator extends Builder
{
    public function update(array $values): int
    {
        (new ModelChangeRecorderEvents())->updatingThrowQuery($values, $this);

        return parent::update($values);
    }
    // todo - дорабоать
    public function insert(array $attributes = []): bool
    {
        return parent::insert($attributes);
    }
}