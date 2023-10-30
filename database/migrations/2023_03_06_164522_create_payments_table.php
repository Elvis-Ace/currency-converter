<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment');
            $table->string('MerchantRequestID');
            $table->string('CheckoutRequestID');
            $table->string('ResponseCodeRe');
            $table->string('ResponseDescription')->nullable();
            $table->string('CustomerMessage')->nullable();
            $table->string('ResponseCodeCo')->nullable();
            $table->string('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('confirm')->nullable();
            $table->integer('amount');
            $table->dateTime('date')->nullable();
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
        Schema::dropIfExists('payments');
    }
};