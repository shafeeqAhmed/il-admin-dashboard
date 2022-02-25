<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoatekSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boatek_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('boatek_setting_uuid')->unique();
            $table->float('vat');
            $table->float('boatek_commission_charges');
            $table->float('transaction_charges');
            $table->integer('withdraw_scheduled_duration')->comment('duration in no of week of withdraw');
            $table->boolean('is_archived')->default(false);
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
        Schema::dropIfExists('boatek_settings');
    }
}
