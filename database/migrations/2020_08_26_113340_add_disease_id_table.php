<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiseaseIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('helth_summary_timeline', function (Blueprint $table) {
            $table->integer('disease_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void 
     */
    public function down()
    {
        Schema::table('helth_summary_timeline', function (Blueprint $table) {
            $table->dropColumn(['disease_id']);
        });
    }
}
