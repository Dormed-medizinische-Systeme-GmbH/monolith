import type { Component } from 'svelte';
import Boxes from '@lucide/svelte/icons/boxes';
import Building from '@lucide/svelte/icons/building';
import Factory from '@lucide/svelte/icons/factory';
import Calendar from '@lucide/svelte/icons/calendar';
import CalendarClock from '@lucide/svelte/icons/calendar-clock';
import ClipboardCheck from '@lucide/svelte/icons/clipboard-check';
import FileText from '@lucide/svelte/icons/file-text';
import HardDrive from '@lucide/svelte/icons/hard-drive';
import LayoutDashboard from '@lucide/svelte/icons/layout-dashboard';
import MapPin from '@lucide/svelte/icons/map-pin';
import Plane from '@lucide/svelte/icons/plane';
import ReceiptText from '@lucide/svelte/icons/receipt-text';
import Settings from '@lucide/svelte/icons/settings';
import ShieldCheck from '@lucide/svelte/icons/shield-check';
import TrendingUp from '@lucide/svelte/icons/trending-up';
import UserCog from '@lucide/svelte/icons/user-cog';
import Users from '@lucide/svelte/icons/users';
import Wrench from '@lucide/svelte/icons/wrench';
import { dashboard } from '@/routes/erp';
import { index as articles } from '@/routes/erp/articles';
import { index as companies } from '@/routes/erp/companies';
import { index as employees } from '@/routes/erp/employees';
import { index as sites } from '@/routes/erp/sites';

export type NavItem = {
    title: string;
    icon: Component;
    /** Fehlt, solange die Fläche noch nicht gebaut ist. */
    href?: string;
};

export type NavGroup = {
    label: string;
    items: NavItem[];
};

/**
 * Die Navigation des ERP — eine Quelle für Seitenleiste UND Befehlspalette.
 *
 * Die Gruppen folgen den Domänen aus ADR-031. Einträge ohne `href` sind noch
 * nicht gebaut: sie stehen bewusst da, ausgegraut und nicht anklickbar, damit
 * die Struktur sichtbar ist — ein toter Verweis, der ins Leere führt, wäre das
 * Gegenteil davon. Wer eine Fläche baut, trägt hier den `href` nach, sonst
 * nichts.
 */
export const navigation: NavGroup[] = [
    {
        label: 'Arbeitsbereich',
        items: [
            {
                title: 'Dashboard',
                icon: LayoutDashboard,
                href: dashboard().url,
            },
            { title: 'Kalender', icon: Calendar },
            { title: 'Termine', icon: CalendarClock },
            { title: 'Urlaube', icon: Plane },
        ],
    },
    {
        label: 'Stammdaten',
        items: [
            { title: 'Firmen', icon: Building, href: companies().url },
            { title: 'Ansprechpartner', icon: Users },
            { title: 'Standorte', icon: MapPin },
        ],
    },
    {
        label: 'Service',
        items: [
            { title: 'Servicefälle', icon: Wrench },
            { title: 'Wartungen', icon: ClipboardCheck },
            { title: 'Geräte', icon: HardDrive },
        ],
    },
    {
        label: 'Vertrieb & Abrechnung',
        items: [
            { title: 'Verkaufschancen', icon: TrendingUp },
            { title: 'Rechnungen', icon: ReceiptText },
            { title: 'Verträge', icon: FileText },
            { title: 'Artikel', icon: Boxes, href: articles().url },
        ],
    },
    {
        label: 'Verwaltung',
        items: [
            { title: 'Mitarbeiter', icon: UserCog, href: employees().url },
            { title: 'Betriebsstätten', icon: Factory, href: sites().url },
            { title: 'Administration', icon: ShieldCheck },
            { title: 'Einstellungen', icon: Settings },
        ],
    },
];

/**
 * Trifft der aktuelle Pfad diesen Eintrag?
 *
 * Die Wurzel muss exakt passen — sonst wäre sie bei jedem Unterpfad aktiv und
 * damit immer. Alles andere zählt auch für seine Unterseiten, damit
 * `/firmen/{id}` weiterhin „Firmen" hervorhebt.
 */
export function isCurrent(href: string | undefined, url: string): boolean {
    if (!href) {
        return false;
    }

    const path = new URL(href, 'http://localhost').pathname;
    const current = url.split('?')[0];

    return path === '/' ? current === '/' : current.startsWith(path);
}
