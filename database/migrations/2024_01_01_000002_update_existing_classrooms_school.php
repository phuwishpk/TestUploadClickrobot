<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\School;
use App\Models\Classroom;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('school_id')
                ->nullable()
                ->constrained('schools')
                ->onDelete('cascade')
                ->after('id');
        });

        $school = School::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Default School',
                'slug' => 'default_school',
                'description' => 'Default school for unassigned classrooms',
            ]
        );

        Classroom::whereNull('school_id')->update(['school_id' => $school->id]);

        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });

        Schema::dropIfExists('schools');
    }
};
