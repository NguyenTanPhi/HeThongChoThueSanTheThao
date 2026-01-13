<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGiaToGoidamuaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goidamua', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('gia')->after('goi_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('goidamua', function (Blueprint $table) {
                 $table->dropColumn('gia');
        });
    }
}
