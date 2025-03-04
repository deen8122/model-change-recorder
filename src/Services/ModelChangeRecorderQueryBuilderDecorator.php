<?php


namespace Deen812\ModelChangeRecorder\Services;

use Deen812\ModelChangeRecorder\Events\ModelChangeRecorderEvents;
use Illuminate\Database\Eloquent\Builder;

class ModelChangeRecorderQueryBuilderDecorator extends Builder
{
    public function update(array $values): int
    {
        // todo - доработать. Вынести ModelChangeRecorderEvents.updatingThrowQuery в отдельный класс
        (new ModelChangeRecorderEvents())->updatingThrowQuery($values, $this);

        return parent::update($values);
    }
    // todo - доработать
    public function insert(array $attributes = []): bool
    {
        return parent::insert($attributes);
    }

    // todo - доработать
    public function delete($id = null)
    {
        return parent::delete($id);
    }
}