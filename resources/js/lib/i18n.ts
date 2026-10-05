import { usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import type { SharedProps } from '../types';

type Tree = Record<string, unknown>;

/**
 * Walks a dotted path through the translation tree.
 *
 * Missing entries resolve to the key itself rather than to an empty string: an
 * empty string hides the mistake on the page, a visible key does not.
 */
function lookup(translations: Tree, key: string): unknown {
    const segments = key.split('.');

    let cursor: unknown = translations;

    for (const segment of segments) {
        if (cursor === null || typeof cursor !== 'object') {
            return undefined;
        }

        cursor = (cursor as Tree)[segment];
    }

    return cursor;
}

/**
 * Resolves a dotted key against the translation bag the server sends.
 *
 * Copy lives in PHP lang files and arrives with the page, so there is exactly
 * one place to edit a translation. Two sources of truth is how a translation
 * silently goes stale.
 */
export function useT(): (key: string) => string {
    const { translations } = usePage<SharedProps & PageProps>().props;

    return (key: string): string => {
        const value = lookup(translations as unknown as Tree, key);

        return typeof value === 'string' ? value : key;
    };
}

/**
 * Reads a list out of the bag, for suggestions and other short enumerations.
 */
export function useList(): (key: string) => Array<string | Record<string, string>> {
    const { translations } = usePage<SharedProps & PageProps>().props;

    return (key: string): Array<string | Record<string, string>> => {
        const value = lookup(translations as unknown as Tree, key);

        return Array.isArray(value) ? (value as Array<string | Record<string, string>>) : [];
    };
}

/**
 * Sends the browser to the same page in another language.
 *
 * A full navigation rather than an Inertia visit, because changing the language
 * replaces the server-rendered copy in every section at once.
 */
export function useLocaleSwitch(): (locale: string) => void {
    return (locale: string): void => {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', locale);
        window.location.assign(url.toString());
    };
}