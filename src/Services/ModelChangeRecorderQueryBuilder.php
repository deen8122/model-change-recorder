<?php


namespace Deen812\ModelChangeRecorder\Services;

use Deen812\ModelChangeRecorder\Events\ModelChangeRecorderEvents;
use Illuminate\Database\Eloquent\Builder;

class ModelChangeRecorderQueryBuilder extends Builder
{
    public function update(array $values): int
    {
        (new ModelChangeRecorderEvents())->updatingThrowQuery($values, $this);

        return parent::update($values);
    }

//    public function create(array $attributes = [])
//    {
//        $newRecord = parent::create($attributes);
//        (new ModelChangeRecorderEvents())->created($newRecord);
//
//        return $newRecord;
//    }

    /**
     *
     * Метод insert используется для массового добавления записей в таблицу базы данных.
     * Он работает на уровне конструктора запросов (Query Builder) и не учитывает Eloquent-модели.
     * Особенности:
     * ---Не вызывает события модели: События Eloquent, такие как creating, created, saving, saved, не срабатывают.
     * ---Не заполняет поля timestamps: Если в таблице есть поля created_at и updated_at, они не будут автоматически заполнены.
     * ---Возвращает boolean: Возвращает true, если вставка прошла успешно, и false в случае ошибки.
     * ---Подходит для массовой вставки: Можно вставить несколько записей за один вызов.
     *
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes = [])
    {
        return parent::insert($attributes);
    }

    // todo - доработать
    public function delete($id = null)
    {
        return parent::delete($id);
    }
}