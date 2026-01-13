<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThanhToanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thanh_toan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('dat_san_id')->constrained('dat_san')->onDelete('cascade');
            $table->string('ma_giao_dich')->nullable();
            $table->decimal('so_tien',12,2);
            $table->enum('phuong_thuc',['tien_mat', 'momo', 'vnpay', 'zalopay'])->default('tien_mat');
            $table->dateTime('ngay_thanh_toan')->nullable();
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
        Schema::dropIfExists('thanh_toan');
    }
}
