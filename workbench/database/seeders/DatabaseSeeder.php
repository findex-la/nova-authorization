<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@laravel.com',
        ]);

        // Create Nova user
        User::factory()->create([
            'name' => 'Laravel Nova',
            'email' => 'nova@laravel.com',
        ]);

        // Create sample products
        Product::factory()->create([
            'name' => 'MacBook Pro',
            'description' => 'Apple MacBook Pro 14-inch with M3 chip',
            'price' => 1999.99,
            'stock' => 25,
        ]);

        Product::factory()->create([
            'name' => 'iPhone 15 Pro',
            'description' => 'Latest iPhone with titanium design',
            'price' => 999.99,
            'stock' => 50,
        ]);

        Product::factory()->create([
            'name' => 'Dell XPS 13',
            'description' => 'Ultrabook laptop with Intel processors',
            'price' => 1299.99,
            'stock' => 15,
        ]);
    }
}
