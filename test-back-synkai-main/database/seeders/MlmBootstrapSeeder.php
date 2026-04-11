<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\Rank;
use Illuminate\Database\Seeder;

class MlmBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        Rank::query()->upsert([
            [
                'slug' => 'sin_rango',
                'name' => 'Sin rango',
                'sort_order' => 0,
                'max_residual_generations' => 0,
                'residual_rate_override' => null,
                'leadership_rate' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'activo',
                'name' => 'Socio activo',
                'sort_order' => 10,
                'max_residual_generations' => 12,
                'residual_rate_override' => null,
                'leadership_rate' => 0.10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'plata',
                'name' => 'Plata',
                'sort_order' => 20,
                'max_residual_generations' => 3,
                'residual_rate_override' => null,
                'leadership_rate' => 0.10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'oro',
                'name' => 'Oro',
                'sort_order' => 30,
                'max_residual_generations' => 3,
                'residual_rate_override' => null,
                'leadership_rate' => 0.10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'zafiro',
                'name' => 'Zafiro',
                'sort_order' => 40,
                'max_residual_generations' => 3,
                'residual_rate_override' => null,
                'leadership_rate' => 0.10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['slug'], ['name', 'sort_order', 'max_residual_generations', 'residual_rate_override', 'leadership_rate', 'updated_at']);

        Category::query()->firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General']
        );

        $packages = [
            ['slug' => 'basico', 'name' => 'Básico', 'price' => '1050.00', 'pv' => '200'],
            ['slug' => 'avanzado', 'name' => 'Avanzado', 'price' => '2700.00', 'pv' => '400'],
            ['slug' => 'profesional', 'name' => 'Profesional', 'price' => '5400.00', 'pv' => '800'],
            ['slug' => 'fundador', 'name' => 'Fundador', 'price' => '10800.00', 'pv' => '1600'],
        ];

        foreach ($packages as $p) {
            Package::query()->updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'pv_points' => $p['pv'],
                    'commissionable_amount' => null,
                    'estado' => 'activo',
                ]
            );
        }

        $catId = Category::query()->where('slug', 'general')->value('id');
        $productos = [
            ['name' => 'Pack Bienvenida Essential', 'description' => 'Kit de inicio con productos estrella. Ideal para nuevos socios.', 'price' => '149.00', 'pv' => '120', 'stock' => 500],
            ['name' => 'Suplemento Vitalidad', 'description' => 'Fórmula con vitaminas y minerales para energía y bienestar diario.', 'price' => '89.00', 'pv' => '70', 'stock' => 300],
            ['name' => 'Crema Facial Antiedad', 'description' => 'Hidratación intensiva y efecto lifting.', 'price' => '65.00', 'pv' => '50', 'stock' => 200],
            ['name' => 'Aceite Esencial Relax', 'description' => 'Blend natural para aromaterapia y masajes.', 'price' => '32.00', 'pv' => '25', 'stock' => 400],
            ['name' => 'Barrita Proteína Chocolate', 'description' => 'Snack saludable con proteína.', 'price' => '28.00', 'pv' => '20', 'stock' => 600],
            ['name' => 'Set Regalo Premium', 'description' => 'Caja regalo con best sellers.', 'price' => '199.00', 'pv' => '160', 'stock' => 100],
        ];
        foreach ($productos as $pr) {
            Product::query()->firstOrCreate(
                ['name' => $pr['name']],
                [
                    'description' => $pr['description'],
                    'price' => $pr['price'],
                    'stock' => $pr['stock'],
                    'image_url' => null,
                    'category_id' => $catId,
                    'pv_points' => $pr['pv'],
                    'estado' => 'activo',
                ]
            );
        }
    }
}

