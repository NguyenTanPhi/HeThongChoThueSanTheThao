<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('san', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('nguoi_dung')->onDelete('cascade');
            $table->string('ten_san');
            $table->string('loai_san')->nullable();
            $table->decimal('gia_thue',12,2)->nullable();
            $table->string('dia_chi')->nullable();
            $table->text('mo_ta')->nullable();
            $table->string('hinh_anh')->nullable();
            $table->enum('trang_thai',['hoat_dong', 'tam_ngung'])->default('hoat_dong');
            $table->enum('trang_thai_duyet',['cho_duyet', 'da_duyet', 'tu_choi'])->default('cho_duyet');
            $table->text('ly_do_tu_choi')->nullable();

           $table->timestamp('ngay_duyet')->nullable();
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
        Schema::dropIfExists('san');
    }
}
