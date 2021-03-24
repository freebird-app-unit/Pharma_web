<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('process_user_id');
			$table->integer('pharmacy_id');
			$table->integer('deliveryboy_id');
			$table->integer('customer_id');
			$table->integer('address_id');
			$table->string('order_number');
			$table->string('prescription');
			$table->string('order_type');
			$table->string('total_days')->nullable();
			$table->text('order_note')->nullable();
			$table->string('reminder_days')->nullable();
			$table->string('order_status')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
