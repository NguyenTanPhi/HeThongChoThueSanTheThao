<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoiDichVuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goidichvu', function (Blueprint $table) {
            $table->id();
            $table->string('ten_goi');
            $table->text('mo_ta');
            $table->decimal('gia',12,2);
            $table->integer('thoi_han');
            $table->enum('trang_thai',['hoat_dong', 'ngung_ban'])->default('hoat_dong');
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
        Schema::dropIfExists('goi_dich_vu');
    }
}
