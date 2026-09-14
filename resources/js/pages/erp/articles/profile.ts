/**
 * Was `App\Modules\Inventory\Queries\ArticleProfile` liefert.
 */
export type ArticleProfile = {
    id: string;
    name: string;
    articleNumber: string;
    description: string | null;
    notes: string | null;
    group: { id: string; name: string; path: string[] };
    flags: {
        active: boolean;
        public: boolean;
        orderable: boolean;
        serialTracked: boolean;
        serviceItem: boolean;
    };
    preise: Record<string, string>;
    stammdaten: Record<string, string>;
    /** Der Feldkatalog der Gruppe, je Feld mit dem Wert dieses Artikels. */
    merkmale: {
        id: string;
        label: string;
        mandatory: boolean;
        value: string | null;
    }[];
};
