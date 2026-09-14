import type { Channel } from '../profile';

/**
 * Was `App\Modules\Crm\Queries\CompanyContactProfile` liefert.
 */
export type ContactProfile = {
    id: string;
    role: string | null;
    department: string | null;
    isPrimary: boolean;
    company: { id: string; name: string };
    person: {
        id: string;
        name: string;
        stammdaten: Record<string, string>;
    };
    channels: Channel[];
    account: {
        email: string;
        active: boolean;
        lastLoginAt: string | null;
        verifiedAt: string | null;
    } | null;
    weitereFirmen: { id: string; name: string; role: string | null }[];
    consents: {
        id: string;
        channel: string;
        status: string;
        granted: boolean;
        date: string | null;
        source: string;
    }[];
};
