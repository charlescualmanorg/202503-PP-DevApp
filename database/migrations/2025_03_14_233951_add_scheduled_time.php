<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->timestamp('scheduled_time')->nullable()->after('fare');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn('scheduled_time');
        });
    }
}
