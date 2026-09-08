<?php

namespace Tests\Unit\Support;

use App\Support\ApplicationContext;
use Tests\TestCase;

class ApplicationContextTest extends TestCase
{
    public function test_resolves_a_configured_host_to_its_context(): void
    {
        config()->set('domains.portal', 'portal.dormed.test');

        $this->assertSame(
            ApplicationContext::Portal,
            ApplicationContext::tryFromHost('portal.dormed.test'),
        );
    }

    public function test_returns_null_for_an_unknown_host(): void
    {
        $this->assertNull(ApplicationContext::tryFromHost('www.example.com'));
    }

    public function test_host_returns_the_configured_domain(): void
    {
        config()->set('domains.shop', 'shop.dormed.test');

        $this->assertSame('shop.dormed.test', ApplicationContext::Shop->host());
    }

    public function test_label_is_human_readable(): void
    {
        $this->assertSame('CRM', ApplicationContext::Crm->label());
        $this->assertSame('Customer Portal', ApplicationContext::Portal->label());
    }
}
