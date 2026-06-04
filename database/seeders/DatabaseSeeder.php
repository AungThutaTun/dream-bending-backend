<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin User ───────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Dream Bending Admin',
            'email'    => 'admin@dreambending.com',
            'phone'    => '09123456789',
            'address'  => 'No.1, Dream Street',
            'city'     => 'Yangon',
            'password' => Hash::make('Admin@123'),
            'role'     => 'admin',
        ]);

        // ─── Test Customer ────────────────────────────────────────
        $customer = User::create([
            'name'     => 'Test Customer',
            'email'    => 'customer@dreambending.com',
            'phone'    => '09987654321',
            'address'  => 'No.5, Example St',
            'city'     => 'Mandalay',
            'password' => Hash::make('Customer@123'),
            'role'     => 'customer',
        ]);

        // ─── Categories ───────────────────────────────────────────
        $categories = [
            ['name' => 'Skincare',    'icon' => '✨'],
            ['name' => 'Sunscreen',   'icon' => '☀️'],
            ['name' => 'Serum',       'icon' => '💧'],
            ['name' => 'Face Mask',   'icon' => '🎭'],
            ['name' => 'Moisturizer', 'icon' => '🌿'],
            ['name' => 'Cleanser',    'icon' => '🧼'],
            ['name' => 'Eye Cream',   'icon' => '👁️'],
            ['name' => 'Toner',       'icon' => '💦'],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[] = Category::create([
                'name'      => $cat['name'],
                'slug'      => Str::slug($cat['name']),
                'icon'      => $cat['icon'],
                'is_active' => true,
            ]);
        }

        // ─── Products ─────────────────────────────────────────────
        $products = [
            [
                'category' => 'Skincare',
                'name'     => 'COSRX Advanced Snail 96 Mucin Power Essence',
                'origin'   => 'Korea',
                'price'    => 25000,
                'stock'    => 50,
                'badge'    => 'Best Seller',
                'thumbnail'=> 'https://placehold.co/400x400/F2D5CC/2C2825?text=COSRX+Snail',
            ],
            [
                'category' => 'Sunscreen',
                'name'     => 'Anessa Perfect UV Sunscreen SPF50+',
                'origin'   => 'Japan',
                'price'    => 38000,
                'stock'    => 30,
                'badge'    => 'Popular',
                'thumbnail'=> 'https://placehold.co/400x400/F2D5CC/2C2825?text=Anessa+UV',
            ],
            [
                'category' => 'Serum',
                'name'     => 'The Ordinary Niacinamide 10% + Zinc 1%',
                'origin'   => 'UK',
                'price'    => 18000,
                'stock'    => 45,
                'badge'    => 'New',
                'thumbnail'=> 'https://placehold.co/400x400/F2D5CC/2C2825?text=Niacinamide',
            ],
            [
                'category' => 'Face Mask',
                'name'     => "MEDIHEAL N.M.F Aquaring Ampoule Mask",
                'origin'   => 'Korea',
                'price'    => 5000,
                'stock'    => 100,
                'badge'    => null,
                'thumbnail'=> 'https://placehold.co/400x400/F2D5CC/2C2825?text=Mediheal+Mask',
            ],
            [
                'category' => 'Moisturizer',
                'name'     => 'Laneige Water Sleeping Mask',
                'origin'   => 'Korea',
                'price'    => 42000,
                'stock'    => 25,
                'badge'    => 'Best Seller',
                'thumbnail'=> 'https://placehold.co/400x400/D9A89A/FDF9F7?text=Laneige+Mask',
            ],
            [
                'category' => 'Cleanser',
                'name'     => 'Cetaphil Gentle Skin Cleanser',
                'origin'   => 'USA',
                'price'    => 22000,
                'stock'    => 60,
                'badge'    => null,
                'thumbnail'=> 'https://placehold.co/400x400/D9A89A/FDF9F7?text=Cetaphil',
            ],
            [
                'category' => 'Toner',
                'name'     => 'Klairs Supple Preparation Facial Toner',
                'origin'   => 'Korea',
                'price'    => 28000,
                'stock'    => 35,
                'badge'    => 'New',
                'thumbnail'=> 'https://placehold.co/400x400/D9A89A/FDF9F7?text=Klairs+Toner',
            ],
            [
                'category' => 'Eye Cream',
                'name'     => 'Kiehl\'s Creamy Eye Treatment with Avocado',
                'origin'   => 'USA',
                'price'    => 65000,
                'stock'    => 15,
                'badge'    => 'Premium',
                'thumbnail'=> 'https://placehold.co/400x400/C9963A/FDF9F7?text=Kiehl\'s+Eye',
            ],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $cat = collect($categoryModels)->firstWhere('name', $p['category']);
            $productModels[] = Product::create([
                'category_id' => $cat->id,
                'name'        => $p['name'],
                'slug'        => Str::slug($p['name']),
                'description' => "Premium {$p['name']} — authentic {$p['origin']} beauty product.",
                'origin'      => $p['origin'],
                'price'       => $p['price'],
                'stock'       => $p['stock'],
                'thumbnail'   => $p['thumbnail'],
                'badge'       => $p['badge'],
                'is_active'   => true,
            ]);
        }

        // ─── Sample Cart & Wishlist for customer ──────────────────
        Cart::create([
            'user_id'    => $customer->id,
            'product_id' => $productModels[0]->id,
            'quantity'   => 2,
        ]);

        Wishlist::create([
            'user_id'    => $customer->id,
            'product_id' => $productModels[1]->id,
        ]);
        Wishlist::create([
            'user_id'    => $customer->id,
            'product_id' => $productModels[4]->id,
        ]);
    }
}
