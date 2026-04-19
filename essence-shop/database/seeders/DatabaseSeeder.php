<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'              => 'Administrador',
            'email'             => 'admin@essenceshop.com.br',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ])->assignRole('admin');

        // Cliente de teste
        User::create([
            'name'              => 'Cliente Teste',
            'email'             => 'cliente@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
        ]);
    }
}

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'O Boticário',       'description' => 'A marca mais querida dos brasileiros.'],
            ['name' => 'Natura',             'description' => 'Bem-estar bem. Estar bem.'],
            ['name' => 'Avon',               'description' => 'A empresa que mais entende de mulher.'],
            ['name' => 'Eudora',             'description' => 'Beleza que inspira.'],
            ['name' => 'Quasar',             'description' => 'Linha masculina O Boticário.'],
            ['name' => 'Floratta',           'description' => 'Perfumes florais femininos exclusivos.'],
            ['name' => 'Malbec',             'description' => 'A essência do homem sofisticado.'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Femininos
            ['name' => 'Perfumes Femininos', 'gender' => 'female', 'description' => 'Fragrâncias delicadas e marcantes para ela.', 'sort_order' => 1],
            ['name' => 'Body Splash',        'gender' => 'female', 'description' => 'Leveza e frescor para o dia a dia.',           'sort_order' => 2],
            ['name' => 'Hidratantes',        'gender' => 'female', 'description' => 'Pele hidratada e perfumada.',                  'sort_order' => 3],
            ['name' => 'Kits Femininos',     'gender' => 'female', 'description' => 'Conjuntos especiais para presentear.',         'sort_order' => 4],

            // Masculinos
            ['name' => 'Perfumes Masculinos','gender' => 'male',   'description' => 'Fragrâncias intensas e sofisticadas para ele.','sort_order' => 5],
            ['name' => 'Desodorantes',       'gender' => 'male',   'description' => 'Proteção e frescor o dia todo.',               'sort_order' => 6],
            ['name' => 'Pós-Barba',          'gender' => 'male',   'description' => 'Cuidado e hidratação após o barbear.',         'sort_order' => 7],
            ['name' => 'Kits Masculinos',    'gender' => 'male',   'description' => 'Conjuntos especiais para presentear.',         'sort_order' => 8],

            // Unissex
            ['name' => 'Perfumes Unissex',   'gender' => 'unisex', 'description' => 'Fragrâncias para todos.',                      'sort_order' => 9],
            ['name' => 'Difusores',          'gender' => 'unisex', 'description' => 'Ambiente perfumado na sua casa.',              'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Femininos
            [
                'name'          => 'Floratta Rose Gold EDP',
                'category_id'   => 1,
                'brand_id'      => 6,
                'gender'        => 'female',
                'price'         => 249.90,
                'price_sale'    => 199.90,
                'sku'           => 'FLO-RG-75',
                'stock'         => 50,
                'volume'        => '75ml',
                'concentration' => 'Eau de Parfum',
                'description'   => 'Uma ode às flores, Floratta Rose Gold é uma fragrância floral frutal sofisticada.',
                'notes'         => 'Notas de topo: pêra, cassis. Coração: rosa, peônia. Fundo: sândalo, almíscar.',
                'featured'      => true,
                'new_arrival'   => false,
            ],
            [
                'name'          => 'Lily Absolu EDP',
                'category_id'   => 1,
                'brand_id'      => 1,
                'gender'        => 'female',
                'price'         => 279.90,
                'sku'           => 'LIL-AB-75',
                'stock'         => 35,
                'volume'        => '75ml',
                'concentration' => 'Eau de Parfum',
                'description'   => 'A sofisticação do lírio em sua forma mais pura e intensa.',
                'notes'         => 'Notas de topo: bergamota. Coração: lírio, jasmim. Fundo: sândalo branco, almíscar.',
                'featured'      => true,
                'new_arrival'   => true,
            ],
            [
                'name'          => 'Humor Intense EDP',
                'category_id'   => 1,
                'brand_id'      => 1,
                'gender'        => 'female',
                'price'         => 199.90,
                'price_sale'    => 159.90,
                'sku'           => 'HUM-IN-75',
                'stock'         => 20,
                'volume'        => '75ml',
                'concentration' => 'Eau de Parfum',
                'description'   => 'Uma fragrância moderna, vibrante e cheia de personalidade.',
                'notes'         => 'Notas florais frutadas com toque amadeirado.',
                'featured'      => false,
                'new_arrival'   => true,
            ],

            // Masculinos
            [
                'name'          => 'Malbec Gold EDP',
                'category_id'   => 5,
                'brand_id'      => 7,
                'gender'        => 'male',
                'price'         => 329.90,
                'sku'           => 'MAL-GD-100',
                'stock'         => 40,
                'volume'        => '100ml',
                'concentration' => 'Eau de Parfum',
                'description'   => 'A versão mais sofisticada e intensa do icônico Malbec.',
                'notes'         => 'Notas de topo: bergamota, pimenta rosa. Coração: cedro, patchouli. Fundo: âmbar, baunilha, almíscar.',
                'featured'      => true,
                'new_arrival'   => false,
            ],
            [
                'name'          => 'Quasar Future EDT',
                'category_id'   => 5,
                'brand_id'      => 5,
                'gender'        => 'male',
                'price'         => 219.90,
                'price_sale'    => 179.90,
                'sku'           => 'QUA-FU-100',
                'stock'         => 60,
                'volume'        => '100ml',
                'concentration' => 'Eau de Toilette',
                'description'   => 'Dinâmico, contemporâneo e cheio de energia. A fragrância do homem do futuro.',
                'notes'         => 'Notas de topo: limão siciliano, cardamomo. Coração: lavanda, madeira. Fundo: âmbar, cedro.',
                'featured'      => true,
                'new_arrival'   => false,
            ],
            [
                'name'          => 'Malbec Black EDP',
                'category_id'   => 5,
                'brand_id'      => 7,
                'gender'        => 'male',
                'price'         => 249.90,
                'sku'           => 'MAL-BK-100',
                'stock'         => 30,
                'volume'        => '100ml',
                'concentration' => 'Eau de Parfum',
                'description'   => 'O lado sombrio e irresistível do Malbec. Intenso, marcante e sofisticado.',
                'notes'         => 'Notas amadeiradas e especiadas com fundo de couro.',
                'featured'      => false,
                'new_arrival'   => true,
            ],

            // Unissex
            [
                'name'          => 'Botica 214 Gardênia & Santalol EDP',
                'category_id'   => 9,
                'brand_id'      => 1,
                'gender'        => 'unisex',
                'price'         => 349.90,
                'sku'           => 'BOT-GS-75',
                'stock'         => 25,
                'volume'        => '75ml',
                'concentration' => 'Eau de Parfum',
                'description'   => 'Uma coleção de perfumaria de nicho que une ingredientes raros em composições únicas.',
                'notes'         => 'Notas de topo: gardênia. Coração: santalol, ylang-ylang. Fundo: almíscar, vetiver.',
                'featured'      => true,
                'new_arrival'   => true,
            ],
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Coupon::insert([
            [
                'code'         => 'BEMVINDO10',
                'type'         => 'percent',
                'value'        => 10,
                'min_purchase' => 100,
                'max_uses'     => null,
                'active'       => true,
                'expires_at'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'code'         => 'FRETEGRATIS',
                'type'         => 'fixed',
                'value'        => 29.90,
                'min_purchase' => 200,
                'max_uses'     => 500,
                'active'       => true,
                'expires_at'   => now()->addMonths(3),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'code'         => 'DESC50',
                'type'         => 'fixed',
                'value'        => 50,
                'min_purchase' => 300,
                'max_uses'     => 100,
                'active'       => true,
                'expires_at'   => now()->addMonth(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
