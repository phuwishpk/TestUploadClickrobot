<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('original_size')->nullable()->after('size');
            $table->bigInteger('compression_saved_bytes')->nullable()->after('original_size');
            $table->decimal('compression_reduction_percent', 6, 1)->nullable()->after('compression_saved_bytes');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'original_size',
                'compression_saved_bytes',
                'compression_reduction_percent',
            ]);
        });
    }
};
