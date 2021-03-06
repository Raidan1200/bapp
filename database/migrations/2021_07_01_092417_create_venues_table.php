<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('email');
            $table->string('logo')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('reminder_delay')->default(7);
            $table->unsignedInteger('check_delay')->default(7);
            $table->unsignedInteger('check_count')->default(0);
            $table->unsignedInteger('cancel_delay')->default(10);
            $table->string('invoice_id_format')->default('%05d');
            $table->unsignedInteger('next_invoice_id')->default(1);
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
        Schema::dropIfExists('venues');
    }
}
