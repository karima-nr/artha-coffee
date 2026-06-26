<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin Art Coffee',
            'email' => 'admin@artcoffee.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Kasir
        User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@artcoffee.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // Create Customer
        User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@artcoffee.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // Categories
        $catEspresso = Category::create(['name' => 'Espresso', 'slug' => 'espresso']);
        $catLatte = Category::create(['name' => 'Latte', 'slug' => 'latte']);
        $catMatcha = Category::create(['name' => 'Matcha', 'slug' => 'matcha']);
        $catSignature = Category::create(['name' => 'Signature', 'slug' => 'signature']);

        // Products
        Product::create([
            'category_id' => $catLatte->id,
            'name' => 'Coffee Latte',
            'description' => 'Klasik & creamy, perpaduan sempurna espresso dan susu lembut',
            'price' => 12000,
            'stock' => 100,
            'image' => 'latte.jpg',
            'status' => 'ready',
        ]);

        Product::create([
            'category_id' => $catMatcha->id,
            'name' => 'Coffee Matcha',
            'description' => 'Perpaduan unik pahitnya kopi dan segar matcha hijau',
            'price' => 14000,
            'stock' => 100,
            'image' => 'matcha.jpg',
            'status' => 'ready',
        ]);

        Product::create([
            'category_id' => $catSignature->id,
            'name' => 'Coffee Vanilla',
            'description' => 'Aroma manis vanila yang menenangkan dengan espresso premium',
            'price' => 13000,
            'stock' => 100,
            'image' => 'vanilla.jpg',
            'status' => 'ready',
        ]);

        Product::create([
            'category_id' => $catEspresso->id,
            'name' => 'Kopi Susu',
            'description' => 'Rasa otentik kopi pilihan + susu segar',
            'price' => 10000,
            'stock' => 100,
            'image' => 'kopi-susu.jpg',
            'status' => 'ready',
        ]);
    }
}
