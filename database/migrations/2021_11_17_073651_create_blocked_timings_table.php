<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocked_timings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('blocked_time_uuid');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('from_time');
            $table->time('to_time');
            $table->string('saved_timezone');
            $table->string('local_timezone');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('blocked_timings');
    }
}
