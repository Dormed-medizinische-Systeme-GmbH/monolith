<?php

namespace App\Support;

/**
 * The application context (subdomain) a request is being served under.
 *
 * A context is a routing and presentation boundary only. It is never an
 * authorization boundary: business rules must still be enforced per request.
 */
enum ApplicationContext: string
{
    case Crm = 'crm';
    case Portal = 'portal';
    case Shop = 'shop';

    /**
     * Resolve the context from a request host, or null for an unknown host.
     */
    public static function tryFromHost(string $host): ?self
    {
        foreach (config('domains') as $context => $domain) {
            if ($host === $domain) {
                return self::from($context);
            }
        }

        return null;
    }

    /**
     * The configured host for this context.
     */
    public function host(): string
    {
        return config("domains.{$this->value}");
    }

    /**
     * Human-readable label for UI and diagnostics.
     */
    public function label(): string
    {
        return match ($this) {
            self::Crm => 'CRM',
            self::Portal => 'Customer Portal',
            self::Shop => 'Shop',
        };
    }
}
