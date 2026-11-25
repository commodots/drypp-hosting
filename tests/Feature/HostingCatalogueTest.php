<?php

namespace Tests\Feature;

use App\Models\HostingPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostingCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_three_expected_usd_hosting_packages(): void
    {
        $this->seed();

        $standard = HostingPackage::where('slug', 'standard-hosting')->firstOrFail();
        $business = HostingPackage::where('slug', 'business-hosting-usd')->firstOrFail();
        $professional = HostingPackage::where('slug', 'professional-hosting')->firstOrFail();

        $this->assertSame('30.00', number_format((float) $standard->price_usd, 2, '.', ''));
        $this->assertSame(3, $standard->disk_space_gb);
        $this->assertSame(30, $standard->bandwidth_gb);
        $this->assertSame(10, $standard->email_accounts);
        $this->assertSame(2, $standard->databases);

        $this->assertSame('50.00', number_format((float) $business->price_usd, 2, '.', ''));
        $this->assertSame('100.00', number_format((float) $professional->price_usd, 2, '.', ''));

        $this->assertTrue($standard->is_active);
        $this->assertTrue($business->is_active);
        $this->assertTrue($professional->is_active);
    }

    public function test_usd_hosting_orders_are_not_sent_to_ngn_paystack_checkout(): void
    {
        $this->markTestIncomplete(
            'Complete this integration test after the payment provider configuration is refactored to support explicit order currencies.'
        );
    }
}
