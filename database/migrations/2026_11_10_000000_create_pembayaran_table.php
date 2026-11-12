<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id_pembayaran');
            $table->unsignedInteger('id_pesan')->unique();
            $table->string('no_resi', 50)->unique();
            $table->string('no_rek', 32);
            $table->date('tgl_transfer');
            $table->time('jam_transfer');
            $table->string('status', 30)->default('Dalam Proses');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
