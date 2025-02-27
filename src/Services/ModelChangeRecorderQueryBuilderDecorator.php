<?php


namespace Deen812\ModelChangeRecorder\Services;

use Deen812\ModelChangeRecorder\Events\ModelChangeRecorderEvents;
use Illuminate\Database\Eloquent\Builder;

class ModelChangeRecorderQueryBuilderDecorator extends Builder
{
    public function update(array $values)
    {
       // dd($values,$this->query->wheres,$this->query->toRawSql());
     //   $this->getQuery()
        (new ModelChangeRecorderEvents())->updatingThrowQuery($values,$this->query , $this->getModel());
        return parent::update($values);


    }
    public function insert(array $attributes = [])
    {

        $this->query->applyAfterQueryCallbacks(function (){
            dd('cccc');
        });
        $resilt = parent::insert($attributes);
        dd($resilt);
        return $resilt;

    }

    public function create(array $attributes = [])
    {

        return parent::create($attributes);
    }
}