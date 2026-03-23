<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions_emargement', function (Blueprint $table) {
            $table->renameColumn('expire_a', 'jeton_expire_a');
            $table->timestamp('cloture_a')->nullable()->after('jeton_expire_a');
        });
    }

    public function down(): void
    {
        Schema::table('sessions_emargement', function (Blueprint $table) {
            $table->dropColumn('cloture_a');
            $table->renameColumn('jeton_expire_a', 'expire_a');
        });
    }
};
