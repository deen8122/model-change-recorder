<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'model_change_recorder',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('table_name');
                $table->enum('action', ['Update', 'Delete', 'Create']);
                $table->string('model_id');
                $table->string('updated_by');
                $table->json('old_json');
                $table->json('new_json');
                $table->timestamps();
                $table->string('call_by')->nullable();
                $table->ipAddress('server_ip')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_change_recorder');
    }
};
