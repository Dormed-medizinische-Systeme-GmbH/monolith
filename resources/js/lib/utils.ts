import type { LinkComponentBaseProps } from '@inertiajs/core';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(
    href: NonNullable<LinkComponentBaseProps['href']>,
): string {
    return typeof href === 'string' ? href : href.url;
}

/*
 * Typ-Helfer, die die shadcn-svelte-Komponenten unter components/ui erwarten.
 * Sie gehören zum Baukasten, nicht zu dieser Anwendung — wer eine Komponente
 * nachinstalliert, braucht sie hier, sonst schlägt die Typprüfung in allen
 * betroffenen Dateien gleichzeitig fehl.
 */

/** Reicht eine `ref`-Bindung an das darunterliegende DOM-Element durch. */
export type WithElementRef<T, U extends HTMLElement = HTMLElement> = T & {
    ref?: U | null;
};

/** Entfernt den `child`-Snippet-Slot aus den Props einer Komponente. */
export type WithoutChild<T> = T extends { child?: unknown }
    ? Omit<T, 'child'>
    : T;

/** Entfernt den `children`-Slot aus den Props einer Komponente. */
export type WithoutChildren<T> = T extends { children?: unknown }
    ? Omit<T, 'children'>
    : T;

/** Beides zusammen — für Komponenten, die ihren Inhalt selbst bestimmen. */
export type WithoutChildrenOrChild<T> = WithoutChildren<WithoutChild<T>>;
