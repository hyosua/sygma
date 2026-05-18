<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations_gestionnaire', function (Blueprint $table) {
            $table->boolean('demande')->default(false)->after('used_at');
            $table->dateTime('expires_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invitations_gestionnaire', function (Blueprint $table) {
            $table->dropColumn('demande');
            $table->dateTime('expires_at')->nullable(false)->change();
        });
    }
};
