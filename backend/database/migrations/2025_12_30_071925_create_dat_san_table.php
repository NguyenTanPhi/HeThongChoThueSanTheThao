<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatSanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dat_san', function (Blueprint $table) {
            $table->id();
            $table->foreignId('san_id')->constrained('san')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('nguoi_dung')->onDelete('cascade');
            $table->date('ngay_dat');
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->decimal('tong_gia',10,0);
            $table->enum('trang_thai',['cho_duyet', 'da_duyet', 'da_huy', 'da_thanh_toan'])->default('cho_duyet');
            
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
        Schema::dropIfExists('dat_san');
    }
}
