<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterDatSanIdInThanhToanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thanh_toan', function (Blueprint $table) {
            DB::statement('
            ALTER TABLE thanh_toan
            DROP FOREIGN KEY thanh_toan_dat_san_id_foreign
        ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('thanh_toan', function (Blueprint $table) {
              DB::statement('
            ALTER TABLE thanh_toan
            ADD CONSTRAINT thanh_toan_dat_san_id_foreign
            FOREIGN KEY (dat_san_id)
            REFERENCES dat_san(id)
            ON DELETE CASCADE
        ');
        });
    }
}
