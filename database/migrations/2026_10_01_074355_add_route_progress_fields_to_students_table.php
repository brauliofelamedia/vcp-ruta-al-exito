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
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_station')->default(1)->after('residence');
            $table->unsignedTinyInteger('completed_stations')->default(0)->after('current_station');
            $table->unsignedSmallInteger('completed_tasks')->default(0)->after('completed_stations');
            $table->unsignedTinyInteger('progress_percentage')->default(0)->after('completed_tasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['current_station', 'completed_stations', 'completed_tasks', 'progress_percentage']);
        });
    }
};
