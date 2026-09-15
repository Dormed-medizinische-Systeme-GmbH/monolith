/**
 * Was `App\Modules\Core\Queries\SiteProfile` liefert.
 */
export type SiteProfile = {
    id: string;
    name: string;
    notes: string | null;
    photoUrl: string | null;
    address: {
        street: string | null;
        postalCode: string | null;
        city: string | null;
        line: string | null;
    };
    employees: {
        id: string;
        name: string;
        role: string | null;
        photoUrl: string | null;
        isActive: boolean;
    }[];
};
