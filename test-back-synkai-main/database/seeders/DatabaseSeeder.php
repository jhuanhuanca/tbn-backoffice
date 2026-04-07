<?php

namespace Database\Seeders;

use App\Models\Rank;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MlmBootstrapSeeder::class);

        $rankId = Rank::query()->where('slug', 'activo')->value('id');

        User::factory()->create([
            'name' => 'Usuario Demo',
            'email' => 'demo@empresa.com',
            'document_id' => 'CI0000001',
            'phone' => '70000000',
            'birth_date' => '1990-05-15',
            'rank_id' => $rankId,
            'email_verified_at' => now(),
            'activation_paid_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@empresa.com',
            'document_id' => 'CI0000002',
            'phone' => '70000001',
            'birth_date' => '1985-01-10',
            'mlm_role' => 'admin',
            'rank_id' => $rankId,
            'email_verified_at' => now(),
            'activation_paid_at' => now(),
        ]);
    }
}
