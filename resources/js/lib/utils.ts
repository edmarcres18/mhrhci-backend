import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    const href = toUrl(urlToCheck);
    if (!href) return false;
    // treat only path portion (before ? or #) of currentUrl for comparison
    const idxQ = currentUrl.indexOf('?');
    const idxH = currentUrl.indexOf('#');
    const cut = [idxQ, idxH].filter((i) => i >= 0).sort((a, b) => a - b)[0];
    const pathOnly = cut !== undefined ? currentUrl.slice(0, cut) : currentUrl;

    // Exact match
    if (href === pathOnly) return true;
    // Resource-style: nested paths active (e.g., '/products' active on '/products/1/edit')
    if (pathOnly.startsWith(href.endsWith('/') ? href : href + '/')) return true;
    // Also active when same resource with query only (e.g., '/products?search=...')
    if (currentUrl.startsWith(href + '?')) return true;

    return false;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
