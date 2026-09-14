/**
 * Der angemeldete MITARBEITER (`users`, Guard `staff`, ADR-042).
 *
 * Kein `name`-Feld (D-093) und seit ADR-046 eine UUID als Schluessel — der Typ
 * aus dem Starter-Kit stimmte in beidem nicht.
 */
export type User = {
    id: string;
    first_name: string;
    last_name: string;
    email: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    is_admin: boolean;
    is_active: boolean;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    /** Leer auf den Anmeldemasken und ueberall dort, wo niemand angemeldet ist. */
    user: User | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
