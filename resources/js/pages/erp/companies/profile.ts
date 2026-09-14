/**
 * Was `App\Modules\Crm\Queries\CompanyProfile` liefert.
 */
export type Channel = {
    id: number;
    type: string;
    typeLabel: string;
    label: string;
    value: string;
    isPrimary: boolean;
};

export type Address = {
    street: string;
    city: string;
    district: string | null;
    country: string;
};

export type Contact = {
    id: number;
    personId: number;
    name: string;
    role: string | null;
    department: string | null;
    isPrimary: boolean;
    channels: Channel[];
    account: {
        email: string;
        active: boolean;
        lastLoginAt: string | null;
    } | null;
};

export type CompanyProfile = {
    id: number;
    name: string;
    nameAddition: string | null;
    specialty: string | null;
    notes: string | null;
    stammdaten: Record<string, string>;
    avv: { signed: boolean; label: string; signedAt: string | null };
    bank: Record<string, string>;
    address: Address | null;
    channels: Channel[];
    billingCompany: { id: number; name: string } | null;
    responsible: { sales: string | null; service: string | null };
    locations: {
        id: number;
        name: string;
        isPrimary: boolean;
        address: Address | null;
    }[];
    contacts: Contact[];
};
