<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLichSanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lich_san', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('san_id')->constrained('san')->onDelete('cascade');
            $table->foreignId('nguoi_dat_id')
        ->nullable() 
        ->constrained('nguoi_dung')
        ->nullOnDelete();
            $table->date('ngay');
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->decimal('gia',10,0);
            $table->enum('trang_thai',['trong','da_dat', 'da_huy'])->default('trong');
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
        Schema::dropIfExists('lich_san');
    }
}
