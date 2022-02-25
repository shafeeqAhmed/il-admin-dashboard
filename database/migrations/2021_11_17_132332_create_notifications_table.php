<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('notification_uuid')->unique();
            $table->unsignedBigInteger('sender_id');
            $table->foreign('sender_id')->on('users')->references('id');
            $table->unsignedBigInteger('receiver_id');
            $table->foreign('receiver_id')->on('users')->references('id');
            $table->uuid('uuid')->nullable();
            $table->text('message')->nullable();
            $table->string('name')->nullable();
            $table->date('date')->nullable();
            $table->timestamp('purchase_time')->nullable();
            $table->string('notification_type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_archive')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
