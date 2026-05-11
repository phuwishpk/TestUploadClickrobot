<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Multi-tenant fields for subdomain & separate DB
            $table->string('domain')->nullable()->unique()->after('code');
            $table->string('database_name')->nullable()->after('domain');
            $table->string('r2_bucket')->nullable()->after('database_name');

            $table->timestamps();

            $table->index('domain');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
