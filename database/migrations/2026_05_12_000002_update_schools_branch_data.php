<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update schools with proper data for Bangrak, Saraburi, Nonburi branches
        DB::table('schools')->truncate();

        // School 1: Bangrak branch
        DB::table('schools')->insert([
            'id' => 1,
            'name' => 'โรงเรียนบางรัก',
            'slug' => 'bangrak',
            'code' => 'BRK001',
            'domain' => 'bangrak.localhost',
            'r2_bucket' => 'school1-bangrak',
            'description' => 'สาขาบางรัก - โรงเรียนในเขตบางรัก',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // School 2: Saraburi branch
        DB::table('schools')->insert([
            'id' => 2,
            'name' => 'โรงเรียนสระบุรี',
            'slug' => 'saraburi',
            'code' => 'SRB001',
            'domain' => 'saraburi.localhost',
            'r2_bucket' => 'school2-saraburi',
            'description' => 'สาขาสระบุรี - โรงเรียนในจังหวัดสระบุรี',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // School 3: Nonburi branch
        DB::table('schools')->insert([
            'id' => 3,
            'name' => 'โรงเรียนนนทบุรี',
            'slug' => 'nonburi',
            'code' => 'NNB001',
            'domain' => 'nonburi.localhost',
            'r2_bucket' => 'school3-nonburi',
            'description' => 'สาขานนทบุรี - โรงเรียนในจังหวัดนนทบุรี',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Reset auto increment
        DB::statement('ALTER TABLE schools AUTO_INCREMENT = 4');
    }

    public function down(): void
    {
        DB::table('schools')->truncate();
    }
};
