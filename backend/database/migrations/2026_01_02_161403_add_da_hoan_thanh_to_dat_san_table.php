<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDaHoanThanhToDatSanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dat_san', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->boolean('da_hoan_thanh')
              ->default(false)
              ->after('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dat_san', function (Blueprint $table) {
        $table->dropColumn('da_hoan_thanh');
        });
    }
}
