import type { Component } from 'svelte';

/**
 * Was eine Fläche in der Kopfzeile anbieten kann.
 *
 * Die Aktionen gehören zur Seite, angezeigt werden sie im Layout — deshalb
 * gehen sie über Inertias `setLayoutProps` dorthin. Bewusst als DATEN und
 * nicht als fertiges Markup: die Kopfzeile bestimmt so das Aussehen, die Seite
 * nur, was es zu tun gibt.
 *
 * Inertia setzt die Layout-Props bei jedem Seitenwechsel zurück, der keinen
 * Zustand erhält — eine Fläche ohne eigene Aktionen erbt also keine fremden.
 */
export type PageAction = {
    label: string;
    icon?: Component;
    /** Verweis — schließt sich mit `onSelect` aus. */
    href?: string;
    onSelect?: () => void;
    variant?: 'default' | 'outline' | 'ghost' | 'destructive';
    /**
     * Aufeinanderfolgende Aktionen mit demselben Gruppennamen werden in der
     * Kopfzeile zu EINER Schaltergruppe zusammengezogen — ohne Abstand, mit
     * geraden Kanten dazwischen. Gedacht für Umschalter, bei denen genau eine
     * Möglichkeit gilt (Tag/Woche/Monat/Agenda).
     */
    group?: string;
    /** Nur das Symbol. Die Beschriftung bleibt als `aria-label` erhalten. */
    iconOnly?: boolean;
    /**
     * Zerstörend — roter Rand und rote Schrift, aber keine gefüllte Fläche.
     * Gefüllt ist erst die Bestätigung im Dialog: dort ist es die einzige
     * Aktion, hier steht „Speichern" daneben und soll den Blick behalten.
     */
    destructive?: boolean;
};

export type ErpLayoutProps = {
    actions?: PageAction[];
};
