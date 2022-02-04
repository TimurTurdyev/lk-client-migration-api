<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLkMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lk_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lk_import_file_id')
                ->nullable()
                ->constrained('lk_import_files')
                ->onDelete('SET NULL');
            $table->morphs('importable');
            $table->integer('old_id');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lk_migrations');
    }
}
