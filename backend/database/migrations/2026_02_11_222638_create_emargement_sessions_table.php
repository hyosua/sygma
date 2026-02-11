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
        Schema::create('emargement_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_session_id')->constrained()->onDelete('cascade');
            $table->string('method'); // qr, manual
            $table->string('token')->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emargement_sessions');
    }
};
