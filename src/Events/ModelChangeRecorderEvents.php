<?php

namespace Deen812\ModelChangeRecorder\Events;

use Deen812\ModelChangeRecorder\Jobs\ModelChangeRecorderJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

class ModelChangeRecorderEvents
{
    use SerializesModels;

    public function created(Model $item): void
    {
        $diff['old'] = [];
        $diff['new'] = $this->getCleaModel($item);
        dispatch(
            (new ModelChangeRecorderJob(
                $this->getCleaModel($item), $this->detectUser(), $diff, 'Create', self::getAction(),
            ))->onQueue('model_change_recorder')
        );
    }

    private function getCleaModel(Model $model): Model
    {
        foreach ($model->getAttributes() as $key => $item) {
            if ($item instanceof UploadedFile) {
                unset($model->{$key});
            }
        }

        return $model;
    }

    private function detectUser()
    {
        return auth()->user()?->id;
    }

    public static function getAction(): ?string
    {
        $backtrace = debug_backtrace();

        if ($backtrace) {
            $class = null;

            foreach ($backtrace as $item) {
                if (isset($item['function']) && $item['function'] == 'resolve') {
                    $class = self::getClass($item);

                    break;
                }

                if (!empty($item['class']) && str_starts_with($item['class'], 'App') && !str_contains(
                        $item['class'],
                        '\Events\\'
                    )) {
                    $class = self::getClass($item);
                }
            }

            return $class;
        }
    }

    protected static function getClass($item): ?string
    {
        if (isset($item['class'])) {
            $line = '';

            if (isset($item['line'])) {
                $line .= ':' . $item['line'];
            }

            $class = $item['class'] . $line;

            if (strlen($class) > 255) {
                $class = mb_substr($class, strlen($class) - 255, 255);
            }

            return $class;
        }

        return null;
    }

    public function updating(Model $item)
    {
        $currentUser = $this->detectUser();
        $diff = $this->compareModels($item);
        if ($diff === false) {
            return;
        }

        $model = $this->getCleaModel($item);
        $callBy = self::getAction();

        dispatch(
            (new ModelChangeRecorderJob($model, $currentUser, $diff, 'Update', $callBy))->onQueue(
                'model_change_recorder'
            )
        );
    }

    private function compareModels(Model $item)
    {
        $diff = [];
        $old = $item->getOriginal();
        $old = array_map(
            function ($value) {
                if (is_array($value)) {
                    return str_replace(
                        '","',
                        '", "',
                        json_encode($value)
                    );
                }

                return $value;
            },
            $old
        );

        // Get new values
        $new = $item->getAttributes();

        if (!isset($item->listenFields)) {
            if (empty($item->getFillable())) {
                throw new InvalidArgumentException('Missing required property of model $listenFields or $fillable');
            }
            $listen = $item->getFillable();
        } else {
            $listen = $item->listenFields;
        }

        $listenFlip = array_flip($listen);

        if (isset($item->excludedFromHistory)) {
            foreach ($item->excludedFromHistory as $field) {
                if (isset($listenFlip[$field], $listen[$listenFlip[$field]])) {
                    unset($listen[$listenFlip[$field]]);
                }
            }
        }

        foreach ($new as $key => $item) {
            if ($item instanceof UploadedFile) {
                if (isset($listenFlip[$key], $listen[$listenFlip[$key]])) {
                    unset($listen[$listenFlip[$key]]);
                }
            }
        }

        // Looking if listen fields was changed
        foreach ($listen as $field) {
            if (!array_key_exists($field, $new) && !array_key_exists($field, $old)) {
                continue;
            }

            if (($new[$field] ?? null) !== ($old[$field] ?? null)) {
                $diff['old'][$field] = $old[$field] ?? null;
                $diff['new'][$field] = $new[$field] ?? null;
            }
        }

        // return NULL if nothing changed
        return empty($diff) ? false : $diff;
    }

    public function updatingThrowQuery($values, $query)
    {
        $items = $query->get();
        $currentUser = $this->detectUser();
        $callBy = self::getAction();
        foreach ($items as $item) {
            $diff = [];
            $diff['new'] = $values;

            foreach ($values as $k => $j) {
                $diff['old'][$k] = $item->{$k};
            }
            dispatch(
                (new ModelChangeRecorderJob($item, $currentUser, $diff, 'Update', $callBy))->onQueue(
                    'model_change_recorder'
                )
            );
        }
    }

    public function deleting(Model $item): void
    {
        $this->deleteAndDispatch($item);
    }

    private function deleteAndDispatch(Model $item): void
    {
        $currentUser = $this->detectUser();

        $diff = ['old' => $this->getOldData($item), 'new' => []];

        $model = $this->getCleaModel($item);

        $callBy = self::getAction();

        dispatch(
            (new ModelChangeRecorderJob($model, $currentUser, $diff, 'Delete', $callBy))->onQueue(
                'model_change_recorder'
            )
        );
    }

    private function getOldData(Model $item): array
    {
        $old = $item->getOriginal();

        return array_map(
            function ($value) {
                if (is_array($value)) {
                    return str_replace(
                        '","',
                        '", "',
                        json_encode($value)
                    );
                }

                return $value;
            },
            $old
        );
    }

    public function pivotAttached(Model $item, string $relationName, array $pivotIds, array $pivotIdsAttributes): void
    {
        $currentUser = $this->detectUser();

        if ($this->isTrackPivot($item, $relationName)) {
            $relationModel = $item->$relationName();

            foreach (
                $relationModel->wherePivotIn($relationModel->getRelatedPivotKeyName(), $pivotIds)->get() as $relation
            ) {
                $diff = [
                    'old' => [],
                    'new' => $relation->pivot->toArray(),
                ];

                $pivot = $this->getCleaModel($relation->pivot);

                $pivot->setKeyName($item->trackPivot[$relationName]);

                $callBy = self::getAction();

                dispatch(
                    (new ModelChangeRecorderJob($pivot, $currentUser, $diff, 'Create', $callBy))->onQueue(
                        'model_change_recorder'
                    )
                );
            }
        }
    }

    private function isTrackPivot(Model $model, string $relationName): bool
    {
        return isset($model->trackPivot[$relationName]) && method_exists($model::class, $relationName);
    }

    public function pivotDetaching(Model $item, string $relationName, array $pivotIds): void
    {
        $currentUser = $this->detectUser();

        if ($this->isTrackPivot($item, $relationName)) {
            $relationModel = $item->$relationName();

            foreach (
                $relationModel->wherePivotIn($relationModel->getRelatedPivotKeyName(), $pivotIds)->get() as $relation
            ) {
                $diff = [
                    'old' => $relation->pivot->toArray(),
                    'new' => [],
                ];

                $pivot = $this->getCleaModel($relation->pivot);

                $pivot->setKeyName($item->trackPivot[$relationName]);

                $callBy = self::getAction();

                dispatch(
                    (new ModelChangeRecorderJob($pivot, $currentUser, $diff, 'Delete', $callBy))->onQueue(
                        'model_change_recorder'
                    )
                );
            }
        }
    }
}
