<?php

namespace Deen812\ModelChangeRecorder\Services;


use BadMethodCallException;
use Deen812\ModelChangeRecorder\Models\ModelChangeRecorder;
use Deen812\ModelChangeTracker\Models\ModelChangeTracker;
use Illuminate\Database\Eloquent\Model;

class ModelChangeRecorderService
{

    public function recordModel(Model $item, $userId, array|bool $diff, $action, string $callBy = null)
    {
        $primaryKey = is_array($item->getKeyName()) ? json_encode($item->getKeyName()) : $item->getKeyName();

        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverIp = $_SERVER['SERVER_ADDR'];
        } else {
            $serverIp = null;
        }

        $model = new ModelChangeRecorder();
        $model->table_name = $item->getTable();
        $model->action = $action;
        $model->model_id = $item->{$primaryKey};
        $model->updated_by = $userId ?? "none";
        $model->call_by = $callBy;
        $model->server_ip = $serverIp;

        try {
            $model->old_json = json_encode($diff['old']);
            $model->new_json = json_encode($diff['new']);
        } catch (BadMethodCallException $exception) {
        }
        //   $model->log_date = Carbon::now();
        $model->save();
    }
}
