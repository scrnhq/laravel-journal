<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();

            $table->unsignedInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();

            $table->json('causer_snapshot')->nullable();

            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['subject_id', 'subject_type']);
            $table->index(['causer_id', 'causer_type']);
        });
    }

    public function down()
    {
        Schema::drop('activity_logs');
    }
}
