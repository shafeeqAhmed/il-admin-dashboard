<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_uuid')->unique();
            $table->string('name');
            $table->enum('role',['super_admin', 'admin', 'company', 'user'])->nullable();
            $table->string('type')->nullable();
            $table->string('email');
            $table->string('password');
            $table->string('user_token')->nullable();
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
        Schema::dropIfExists('admin');
    }
}
