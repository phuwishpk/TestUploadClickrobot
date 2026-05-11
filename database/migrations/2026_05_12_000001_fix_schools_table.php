<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if schools table exists
        $tableExists = Schema::hasTable('schools');

        if ($tableExists) {
            // Add missing columns only if they don't exist
            Schema::table('schools', function (Blueprint $table) {
                if (!Schema::hasColumn('schools', 'domain')) {
                    $table->string('domain')->nullable()->unique()->after('code');
                }
                if (!Schema::hasColumn('schools', 'database_name')) {
                    $table->string('database_name')->nullable()->after('domain');
                }
                if (!Schema::hasColumn('schools', 'r2_bucket')) {
                    $table->string('r2_bucket')->nullable()->after('database_name');
                }
            });

            // Add index if not exists
            DB::statement('ALTER TABLE schools ADD INDEX domain_idx (domain)');
            DB::statement('ALTER TABLE schools ADD INDEX is_active_idx (is_active)');
        } else {
            // Create new table if not exists
            Schema::create('schools', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('code')->unique();
                $table->string('domain')->nullable()->unique();
                $table->string('database_name')->nullable();
                $table->string('r2_bucket')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('domain');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['domain', 'database_name', 'r2_bucket']);
        });
    }
};
