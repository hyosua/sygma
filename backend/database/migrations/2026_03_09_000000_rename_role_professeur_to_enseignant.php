<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::where('name', 'Professeur')->first();
        if ($role) {
            $role->name = 'Enseignant';
            $role->save();
        }
    }

    public function down(): void
    {
        $role = Role::where('name', 'Enseignant')->first();
        if ($role) {
            $role->name = 'Professeur';
            $role->save();
        }
    }
};
