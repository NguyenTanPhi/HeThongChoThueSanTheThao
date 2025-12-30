<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoiDaMuaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goidamua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('goi_id')->constrained('goidichvu')->onDelete('restrict')->onUpdate('cascade');
            $table->date('ngay_mua');
            $table->date('ngay_het');
            $table->enum('trang_thai',['con_han', 'het_han'])->default('con_han');
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
        Schema::dropIfExists('goi_da_mua');
    }
}
