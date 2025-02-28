<?php

namespace Deen812\ModelChangeRecorder\Models;

use Illuminate\Database\Eloquent\Model;

class ModelChangeRecorder extends Model
{
    protected $table = 'model_change_recorder';

    protected $fillable = [
        'table_name',
        'action',
        'model_id',
        'updated_by',
        'old_json',
        'new_json',
    ];
}
