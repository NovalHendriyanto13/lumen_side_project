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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('no_pengaduan', 10)->unique();
            $table->date('tgl_pengaduan');
            $table->string('deskripsi');
            $table->date('tgl_selesai')->nullable();
            $table->string('foto_pengaduan')->nullable();
            $table->string('status', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
