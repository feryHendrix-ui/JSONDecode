<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 24)->unique();
            $table->string('judul', 250);
            $table->string('penulis', 200);
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('kategori', 30)->index();
            $table->unsignedInteger('stok')->default(1);
            $table->string('open_library_key', 50)->nullable()->unique();
            $table->unsignedBigInteger('cover_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
