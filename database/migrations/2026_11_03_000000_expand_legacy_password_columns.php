<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->string('password', 255)->change();
        });

        Schema::table('member', function (Blueprint $table) {
            $table->string('password', 255)->change();
        });

        Schema::table('operator_loket', function (Blueprint $table) {
            $table->string('password', 255)->change();
        });
    }

    public function down(): void
    {
        throw new LogicException('Password columns cannot be shortened after password hashes are stored.');
    }
};