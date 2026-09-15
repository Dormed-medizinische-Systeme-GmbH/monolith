/**
 * Was `App\Modules\Core\Queries\UserProfile` liefert.
 */
export type EmployeeProfile = {
    id: string;
    firstName: string;
    lastName: string;
    name: string;
    email: string;
    photoUrl: string | null;
    site: { id: string; name: string; addressLine: string | null } | null;
    role: { id: string; key: string; name: string };
    flags: { active: boolean; admin: boolean };
    anmeldung: {
        hasPassword: boolean;
        twoFactorConfirmedAt: string | null;
        entraOid: string | null;
        lastLoginAt: string | null;
    };
    angelegtAm: string | null;
};

export type RoleOption = { id: string; name: string; key: string };
export type SiteOption = { id: string; name: string };
