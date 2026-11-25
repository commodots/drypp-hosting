<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HostingPackage;
use App\Models\DomainPricing;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(AdminUserSeeder::class);


        // Seed the three annual USD hosting plans. USD checkout must be enabled before live sales.
        $plans = [
            ['name'=>'Standard Hosting','slug'=>'standard-hosting','description'=>'A practical starter plan for a small business website and professional email.','price_usd'=>29.99,'disk_space_gb'=>3,'bandwidth_gb'=>30,'email_accounts'=>10,'addon_domains'=>1,'databases'=>2,'max_accounts_per_server'=>100],
            ['name'=>'Business Hosting','slug'=>'business-hosting-usd','description'=>'More space and resources for a growing business website.','price_usd'=>49.99,'disk_space_gb'=>6,'bandwidth_gb'=>60,'email_accounts'=>20,'addon_domains'=>2,'databases'=>4,'max_accounts_per_server'=>60],
            ['name'=>'Professional Hosting','slug'=>'professional-hosting','description'=>'Expanded hosting capacity for larger sites and growing workloads.','price_usd'=>99.99,'disk_space_gb'=>10,'bandwidth_gb'=>100,'email_accounts'=>40,'addon_domains'=>5,'databases'=>10,'max_accounts_per_server'=>30],
        ];
        foreach ($plans as $plan) {
            HostingPackage::updateOrCreate(['slug'=>$plan['slug']], array_merge($plan, [
                'price_ngn'=>0, 'billing_cycle'=>'yearly', 'free_website_enabled'=>in_array($plan['slug'], ['business-hosting-usd','professional-hosting'], true), 'is_active'=>true,
                'features'=>[
                    'cPanel control panel',
                    'SSL certificate subject to hosting provider support',
                    'PHP and common website application support',
                    'DNS and domain management tools',
                    'Customer dashboard and service status',
                ],
            ]));
        }

        foreach ([['.com', 18000], ['.net', 22000], ['.org', 20000], ['.com.ng', 9000]] as [$extension, $cost]) {
            DomainPricing::updateOrCreate(['extension' => $extension], [
                'registration_cost_ngn' => $cost, 'renewal_cost_ngn' => $cost,
                'markup_ngn' => 3000, 'markup_percent' => 10, 'is_active' => true,
            ]);
        }
    }
}
