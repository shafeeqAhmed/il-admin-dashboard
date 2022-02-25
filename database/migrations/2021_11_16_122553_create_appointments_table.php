<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('appointment_uuid');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->on('customers')->references('id');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->on('freelancer_categories')->references('id');
            $table->unsignedBigInteger('promocode_id');
            $table->foreign('promocode_id')->on('promo_codes')->references('id');
            $table->string('booking_identifier');
            $table->string('title');
            $table->enum('type',['appointment'])->default('appointment');
            $table->enum('status',['pending','confirmed','completed','cancelled','rejected'])->default('pending');
            $table->string('currency')->nullable();
            $table->double('price');
            $table->float('price_per_hour')->nullable();
            $table->double('discount')->nullable();
            $table->double('discounted_price')->nullable();
            $table->double('travelling_charges')->nullable();
            $table->double('paid_amount');
            $table->date('appointment_date');
            $table->time('from_time');
            $table->time('to_time');
            $table->bigInteger('appointment_start_date_time');
            $table->bigInteger('appointment_end_date_time');
            $table->string('saved_timezone');
            $table->string('local_timezone');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('address')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->boolean('has_rescheduled')->default(false);
            $table->boolean('is_archive')->default(false);
            $table->enum('created_by',['customer','freelancer'])->default('customer');
            $table->string('location_type')->nullable();
            $table->boolean('public_chat')->default(false);

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
        Schema::dropIfExists('appointments');
    }
}
