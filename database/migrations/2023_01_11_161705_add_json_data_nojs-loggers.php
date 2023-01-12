<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonDataNojsLoggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('nojs_loggers', function (Blueprint $table) {
            $table->json('json_data')->after('pms_state')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nojs_loggers', function (Blueprint $table) {
            $table->dropColumn('json_data');
        });
    }
}