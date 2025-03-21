<?php

namespace Deen812\ModelChangeRecorder\Models;

use Illuminate\Database\Eloquent\Model;

class ModelChangeRecorder extends Model
{

    protected $fillable = [
        'table_name',
        'action',
        'model_id',
        'updated_by',
        'old_json',
        'new_json',
    ];
}
