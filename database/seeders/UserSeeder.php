<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'vendor_status' => 'customer',
            'vendor_tier' => 'Customer',
            'commission_rate' => 0,
        ]);

        // Gold Tier Vendor
        User::create([
            'name' => 'Golden Threads Fashion',
            'email' => 'gold.vendor@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'vendor_status' => 'approved_vendor',
            'vendor_tier' => 'Gold',
            'commission_rate' => 0.0500,
            'business_name' => 'Golden Threads Fashion',
        ]);

        // Silver Tier Vendor
        User::create([
            'name' => 'Silver Electronics',
            'email' => 'silver.vendor@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'vendor_status' => 'approved_vendor',
            'vendor_tier' => 'Silver',
            'commission_rate' => 0.1000,
            'business_name' => 'Silver Electronics',
        ]);

        // Regular Customer
        User::create([
            'name' => 'Regular Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'vendor_status' => 'customer',
            'vendor_tier' => 'Customer',
            'commission_rate' => 0,
        ]);
    }
}