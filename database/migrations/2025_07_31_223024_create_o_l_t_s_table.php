<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('olt', function (Blueprint $table) {
            $table->id();
            $table->string('site')->nullable();
            $table->string('ipolt')->nullable();
            $table->string('portolt')->nullable();
            $table->string('ipvpn')->nullable();
            $table->string('portvpn')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt');
    }
};
