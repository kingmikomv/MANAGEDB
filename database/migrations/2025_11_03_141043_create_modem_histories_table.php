<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('modem_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modem_id')->constrained('modems')->onDelete('cascade');
            $table->string('pelanggan');
            $table->timestamp('tanggal_pasang')->nullable();
            $table->timestamp('tanggal_tarik')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('modem_histories');
    }
};
