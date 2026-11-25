<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('hosting_packages', 'price_usd')) {
            Schema::table('hosting_packages', function (Blueprint $table) {
                $table->decimal('price_usd', 10, 2)->default(0)->after('price_ngn');
            });
        }
        $now = now();
        $plans = [
            ['name'=>'Standard Hosting','slug'=>'standard-hosting','description'=>'A practical starter plan for a small business website and professional email.','price_usd'=>29.99,'disk_space_gb'=>3,'bandwidth_gb'=>30,'email_accounts'=>10,'addon_domains'=>1,'databases'=>2,'max_accounts_per_server'=>100,'features'=>['cPanel control panel','SSL certificate subject to provider support','PHP and common website application support','DNS and domain management tools','Customer dashboard and service status']],
            ['name'=>'Business Hosting','slug'=>'business-hosting-usd','description'=>'More space and resources for a growing business website.','price_usd'=>49.99,'disk_space_gb'=>6,'bandwidth_gb'=>60,'email_accounts'=>20,'addon_domains'=>2,'databases'=>4,'max_accounts_per_server'=>60,'features'=>['cPanel control panel','SSL certificate subject to provider support','PHP and common website application support','DNS and domain management tools','Customer dashboard and service status']],
            ['name'=>'Professional Hosting','slug'=>'professional-hosting','description'=>'Expanded hosting capacity for larger sites and growing workloads.','price_usd'=>99.99,'disk_space_gb'=>10,'bandwidth_gb'=>100,'email_accounts'=>40,'addon_domains'=>5,'databases'=>10,'max_accounts_per_server'=>30,'features'=>['cPanel control panel','SSL certificate subject to provider support','PHP and common website application support','DNS and domain management tools','Customer dashboard and service status']],
        ];
        DB::table('hosting_packages')->whereIn('slug',['starter-hosting','business-hosting','growth-hosting'])->update(['is_active'=>false,'updated_at'=>$now]);
        foreach ($plans as $plan) {
            $plan['price_ngn'] = 0; // USD is the catalogue price; payment flow needs USD gateway support before live billing.
            $plan['billing_cycle']='yearly'; $plan['features']=json_encode($plan['features']);
            $plan['free_website_enabled']=in_array($plan['slug'], ['business-hosting-usd','professional-hosting'], true); $plan['is_active']=true; $plan['updated_at']=$now;
            $exists=DB::table('hosting_packages')->where('slug',$plan['slug'])->exists();
            if ($exists) DB::table('hosting_packages')->where('slug',$plan['slug'])->update($plan);
            else { $plan['created_at']=$now; DB::table('hosting_packages')->insert($plan); }
        }
    }
    public function down(): void
    {
        DB::table('hosting_packages')->whereIn('slug',['standard-hosting','business-hosting-usd','professional-hosting'])->delete();
        if (Schema::hasColumn('hosting_packages','price_usd')) Schema::table('hosting_packages', function (Blueprint $table) { $table->dropColumn('price_usd'); });
    }
};
