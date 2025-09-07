<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLevel;
use Illuminate\Support\Facades\Hash;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Warehouses
        $warehouse1 = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH001',
            'location' => '123 Main Street, City Center',
            'status' => 'active',
        ]);

        $warehouse2 = Warehouse::create([
            'name' => 'Secondary Warehouse',
            'code' => 'WH002',
            'location' => '456 Industrial Ave, Industrial Zone',
            'status' => 'active',
        ]);

        // Create Super Admin User
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@smartinventory.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $superAdmin->assignRole('Super Admin');

        // Create Warehouse Manager
        $warehouseManager = User::create([
            'name' => 'John Manager',
            'email' => 'manager@smartinventory.com',
            'password' => Hash::make('password'),
            'warehouse_id' => $warehouse1->id,
            'status' => 'active',
        ]);
        $warehouseManager->assignRole('Warehouse Manager');

        // Update warehouse with manager
        $warehouse1->update(['manager_id' => $warehouseManager->id]);

        // Create Staff User
        $staff = User::create([
            'name' => 'Jane Staff',
            'email' => 'staff@smartinventory.com',
            'password' => Hash::make('password'),
            'warehouse_id' => $warehouse1->id,
            'status' => 'active',
        ]);
        $staff->assignRole('Staff');

        // Create Categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'level' => 0,
            'path' => 'electronics',
        ]);

        $computers = Category::create([
            'name' => 'Computers',
            'slug' => 'computers',
            'parent_id' => $electronics->id,
            'level' => 1,
            'path' => 'electronics/computers',
        ]);

        $laptops = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'parent_id' => $computers->id,
            'level' => 2,
            'path' => 'electronics/computers/laptops',
        ]);

        $furniture = Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'level' => 0,
            'path' => 'furniture',
        ]);

        // Create Products
        $product1 = Product::create([
            'name' => 'MacBook Pro 16"',
            'sku' => 'MBP16-001',
            'category_id' => $laptops->id,
            'unit' => 'pcs',
            'cost_price' => 2000.00,
            'sell_price' => 2500.00,
            'reorder_level' => 5,
            'description' => 'Apple MacBook Pro 16-inch with M1 Pro chip',
            'barcode' => '123456789012',
            'status' => 'active',
        ]);

        $product2 = Product::create([
            'name' => 'Office Chair',
            'sku' => 'CHAIR-001',
            'category_id' => $furniture->id,
            'unit' => 'pcs',
            'cost_price' => 150.00,
            'sell_price' => 200.00,
            'reorder_level' => 10,
            'description' => 'Ergonomic office chair with lumbar support',
            'barcode' => '123456789013',
            'status' => 'active',
        ]);

        $product3 = Product::create([
            'name' => 'Wireless Mouse',
            'sku' => 'MOUSE-001',
            'category_id' => $computers->id,
            'unit' => 'pcs',
            'cost_price' => 25.00,
            'sell_price' => 35.00,
            'reorder_level' => 20,
            'description' => 'Wireless optical mouse with USB receiver',
            'barcode' => '123456789014',
            'status' => 'active',
        ]);

        // Create Stock Levels
        StockLevel::create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouse1->id,
            'available_qty' => 10,
            'reserved_qty' => 2,
        ]);

        StockLevel::create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouse2->id,
            'available_qty' => 5,
            'reserved_qty' => 0,
        ]);

        StockLevel::create([
            'product_id' => $product2->id,
            'warehouse_id' => $warehouse1->id,
            'available_qty' => 25,
            'reserved_qty' => 5,
        ]);

        StockLevel::create([
            'product_id' => $product3->id,
            'warehouse_id' => $warehouse1->id,
            'available_qty' => 50,
            'reserved_qty' => 10,
        ]);

        StockLevel::create([
            'product_id' => $product3->id,
            'warehouse_id' => $warehouse2->id,
            'available_qty' => 30,
            'reserved_qty' => 0,
        ]);
    }
}
