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
        Schema::table('users', function (Blueprint $table) {
            $table->string('prenom')->after('nom')->nullable();
            $table->boolean('premiere_connexion')->default(true)->after('password');
            $table->string('url_image_profil')->after('premiere_connexion')->nullable();
            $table->string('ine')->after('url_image_profil')->unique()->nullable();
            $table->json('specialites')->after('ine')->nullable();
            $table->foreignId('groupe_id')->after('specialites')->nullable()->constrained('groupes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['groupe_id']);
            $table->dropColumn([
                'prenom',
                'premiere_connexion',
                'url_image_profil',
                'ine',
                'specialites',
                'groupe_id'
            ]);
        });
    }
};
