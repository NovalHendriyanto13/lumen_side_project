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
        Schema::create('request_list', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('no_permintaan', 5)->unique();
            $table->string('no_pickup', 5)->unique()->nullable();
            $table->date('tgl_permintaan');
            $table->string('no_kamar', 5);
            $table->date('tgl_selesai')->nullable();
            $table->string('status', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_list');
    }
};
