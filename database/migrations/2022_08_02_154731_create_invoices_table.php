<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->foreignId('customer_id')->constrained();
            $table->string('courier');
            $table->string('courier_service');
            $table->bigInteger('courier_cost');
            $table->integer('weight');
            $table->string('name');
            $table->string('phone');
            $table->foreignId('city_id')->constrained();
            $table->foreignId('province_id')->constrained();
            $table->text('address');
            $table->enum('status', [
                'pending',
                'success',
                'expired',
                'failed'
            ]);
            $table->bigInteger('grand_total');
            $table->string('snap_token')->nullable();
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
        Schema::dropIfExists('invoices');
    }

};
