<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CampaignFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_campaign_redirects_to_show_page_with_success_message()
    {
        $user = User::factory()->create();
        $group = ContactGroup::create(['user_id' => $user->id, 'name' => 'Test Group']);

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'name' => 'HTML Campaign',
            'subject' => 'Hello',
            'sender_email' => 'sender@test.com',
            'type' => 'cold',
            'format' => 'html',
            'body' => '<h1>Preview Me</h1>',
            'recipient_type' => 'group',
            'group_id' => $group->id,
            'action' => 'draft',
        ]);

        $response->assertRedirect(route('campaigns.index'));
        $response->assertSessionHas('success', 'Campaign saved as draft.');
        $campaign = Campaign::where('name', 'HTML Campaign')->firstOrFail();
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'format' => 'html',
            'sender_email' => 'sender@test.com',
        ]);
        $this->assertDatabaseHas('campaign_emails', [
            'campaign_id' => $campaign->id,
            'subject' => 'Hello',
            'body' => '<h1>Preview Me</h1>',
            'format' => 'html',
        ]);
    }

    public function test_text_campaign_redirects_straight_to_index()
    {
        $user = User::factory()->create();
        $group = ContactGroup::create(['user_id' => $user->id, 'name' => 'Test Group']);

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'name' => 'Text Campaign',
            'subject' => 'Hello',
            'sender_email' => 'sender@test.com',
            'type' => 'cold',
            'format' => 'text',
            'body' => 'Just plain text',
            'recipient_type' => 'group',
            'group_id' => $group->id,
            'action' => 'draft',
        ]);

        $response->assertRedirect(route('campaigns.index'));
        $response->assertSessionHas('success', 'Campaign saved as draft.');
    }

   public function test_user_can_send_to_individual_contacts()
    {
        $user = User::factory()->create();
        
        $packageId = \Illuminate\Support\Facades\DB::table('packages')->insertGetId([
            'name' => 'Test Package',
            'type' => 'cold_email',
            'price_ngn' => 0,
            'email_limit' => 0,
            'lead_limit' => 0,  
        ]);

        // 2. Create the Subscription
        \App\Models\Subscription::create([
            'user_id' => $user->id,
            'package_id' => $packageId,
            'emails_used' => 0,
            'status' => 'active',
            'expires_at' => now()->addMonth(),
        ]);

        $group = ContactGroup::create(['user_id' => $user->id, 'name' => 'Send Group']);
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'recipient_type' => 'group',
            'group_id' => $group->id,
        ]);
        
        $c1 = Contact::factory()->create(['user_id' => $user->id, 'email' => 'a@test.com']);
        $c2 = Contact::factory()->create(['user_id' => $user->id, 'email' => 'b@test.com']);
        \App\Models\ContactGroupItem::create([
            'contact_id' => $c1->id,
            'contact_group_id' => $group->id,
        ]);

        $response = $this->actingAs($user)->post(route('campaigns.send', $campaign), [
        ]);

        $response->assertRedirect(route('campaigns.index'));
        $response->assertSessionHas('success', '1 emails queued!');
        $this->assertDatabaseHas('campaign_messages', ['email' => 'a@test.com']);
        $this->assertDatabaseMissing('campaign_messages', ['email' => 'b@test.com']);
    }
}