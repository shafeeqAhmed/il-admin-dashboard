<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('message_uuid')->unique();
            $table->string('content',400)->nullable();
            $table->string('channel');
            $table->string('local_db_key')->nullable();
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_key');
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_key');
            $table->string('attachment')->nullable();
            $table->enum('attachment_type',['image', 'video', 'audio', 'file'])->nullable();
            $table->string('video_thumbnail')->nullable();
            $table->string('saved_timezone');
            $table->string('local_timezone');
            $table->string('deleted_one')->nullable();
            $table->string('deleted_two')->nullable();
            $table->enum('status',['sent', 'viewed', 'delivered'])->default('sent');
            $table->enum('sender_type',['freelancer', 'customer', 'admin'])->nullable();
            $table->enum('receiver_type',['customer', 'freelancer', 'admin'])->nullable();
            $table->enum('chat_with',['user', 'admin'])->default('user');

            $table->timestamps();
        });
    }
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,
//``,


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
