<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRescheduledAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rescheduled_appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('rescheduled_appointment_uuid')->unique();
            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')->on('appointments')->references('id');
            $table->unsignedBigInteger('rescheduled_by_id');
            $table->enum('rescheduled_by_type',['customer', 'freelancer', 'admin']);
            $table->time('previous_from_time');
            $table->time('previous_to_time');
            $table->date('previous_appointment_date');
            $table->enum('previous_status',['pending','confirmed','completed','cancelled','rejected']);
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
        Schema::dropIfExists('rescheduled_appointments');
    }
}
