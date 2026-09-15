/**
 * Was `App\Modules\Core\Queries\SiteProfile` liefert.
 */
export type SiteProfile = {
    id: string;
    name: string;
    shortName: string | null;
    isActive: boolean;
    notes: string | null;
    photoUrl: string | null;
    address: {
        street: string | null;
        houseNumber: string | null;
        postalCode: string | null;
        city: string | null;
        line: string | null;
    };
    users: {
        id: string;
        name: string;
        role: string | null;
        photoUrl: string | null;
        isActive: boolean;
    }[];
};
