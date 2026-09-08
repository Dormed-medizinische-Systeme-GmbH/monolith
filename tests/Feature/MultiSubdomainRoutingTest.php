<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ApplicationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MultiSubdomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function contextHosts(): array
    {
        return [
            'crm' => ['crm.dormed.test', 'CRM'],
            'portal' => ['portal.dormed.test', 'Customer Portal'],
            'shop' => ['shop.dormed.test', 'Shop'],
        ];
    }

    #[DataProvider('contextHosts')]
    public function test_each_context_host_renders_its_own_landing(string $host, string $label): void
    {
        $response = $this->get("http://{$host}/");

        $response->assertOk();
        $response->assertSee($label);
        $response->assertSee($host);
    }

    public function test_portal_host_does_not_render_another_contexts_landing(): void
    {
        $this->get('http://portal.dormed.test/')
            ->assertOk()
            ->assertDontSee('CRM')
            ->assertDontSee('Shop');
    }

    public function test_unknown_host_falls_back_to_the_welcome_page(): void
    {
        $this->get('http://intranet.dormed.test/')
            ->assertOk()
            ->assertViewIs('welcome');
    }

    public function test_context_route_names_are_prefixed_and_bound_to_their_host(): void
    {
        $this->assertSame('http://crm.dormed.test', route('crm.home'));
        $this->assertSame('http://shop.dormed.test', route('shop.home'));
    }

    public function test_resolved_context_is_shared_with_the_view(): void
    {
        $this->get('http://crm.dormed.test/')
            ->assertViewHas('applicationContext', ApplicationContext::Crm);
    }

    #[DataProvider('contextHosts')]
    public function test_shared_auth_routes_are_reachable_on_every_context_host(string $host, string $label): void
    {
        $this->get("http://{$host}/login")->assertOk();
    }

    public function test_authenticated_user_is_recognised_across_context_hosts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('http://crm.dormed.test/')->assertOk();
        $this->actingAs($user)->get('http://portal.dormed.test/')->assertOk();
    }
}
