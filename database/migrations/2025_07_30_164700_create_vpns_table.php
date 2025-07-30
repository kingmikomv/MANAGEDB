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
       Schema::create('vpn', function (Blueprint $table) {
            $table->id();
            $table->string('namaakun')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('ipaddress')->nullable();
            $table->integer('portapi')->nullable();
            $table->integer('portweb')->nullable();
            $table->integer('portmikrotik')->nullable();
            $table->integer('portwbx')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vpn');
    }
};
