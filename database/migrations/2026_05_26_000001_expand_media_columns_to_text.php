<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE produk MODIFY gambar TEXT NULL');
        DB::statement('ALTER TABLE produk MODIFY foto_2 TEXT NULL');
        DB::statement('ALTER TABLE produk MODIFY foto_3 TEXT NULL');
        DB::statement('ALTER TABLE users MODIFY tentang_banner TEXT NULL');
        DB::statement('ALTER TABLE orders MODIFY payment_proof TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE produk MODIFY gambar VARCHAR(255) NULL');
        DB::statement('ALTER TABLE produk MODIFY foto_2 VARCHAR(255) NULL');
        DB::statement('ALTER TABLE produk MODIFY foto_3 VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY tentang_banner VARCHAR(255) NULL');
        DB::statement('ALTER TABLE orders MODIFY payment_proof VARCHAR(255) NULL');
    }
};