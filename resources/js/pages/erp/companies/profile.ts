/**
 * Was `App\Modules\Crm\Queries\CompanyProfile` liefert.
 */
export type Channel = {
    id: string;
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
    id: string;
    personId: string;
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
    id: string;
    name: string;
    nameAddition: string | null;
    specialty: string | null;
    notes: string | null;
    debitorNumber: string | null;
    stammdaten: Record<string, string>;
    avv: { signed: boolean; label: string; signedAt: string | null };
    bank: Record<string, string>;
    address: Address | null;
    channels: Channel[];
    billingCompany: { id: string; name: string } | null;
    responsible: { sales: string | null; service: string | null };
    locations: {
        id: string;
        name: string;
        notes: string | null;
        isPrimary: boolean;
        address: Address | null;
        /** Einzeln für die Maske — dort wird die Anschrift in Feldern bearbeitet. */
        addressFields: {
            street: string | null;
            houseNumber: string | null;
            postalCode: string | null;
            city: string | null;
        };
        /** Deckungsgleich mit der Sitzadresse — dann wird sie nicht wiederholt. */
        sameAsCompanyAddress: boolean;
    }[];
    contacts: Contact[];
};
