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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('full_name')->nullable();
            $table->enum('system', ['medium', 'elite'])->default('medium');
            $table->enum('residence', ['usa', 'outside'])->default('usa');
            $table->string('ghl_contact_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamps();

            $table->index('last_active_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
