<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address', function (Blueprint $table) {
            $table->text('address2')->after('address')->nullable();
			$table->string('city')->after('address2')->nullable();
			$table->string('state')->after('city')->nullable();
			$table->string('country')->after('state')->nullable();
			$table->string('pincode')->after('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('address', function (Blueprint $table) {
            $table->dropColumn('address2');
			$table->dropColumn('city');
			$table->dropColumn('state');
			$table->dropColumn('country');
			$table->dropColumn('pincode');
        });
    }
}
