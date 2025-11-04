<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('modems', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->enum('status', ['tersedia', 'terpasang', 'ditarik', 'rusak', 'return'])->default('tersedia');
            $table->string('pelanggan_aktif')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('modems');
    }
};
