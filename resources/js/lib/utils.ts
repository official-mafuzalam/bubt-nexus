import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

// lib/utils.ts
export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    const checkUrl = toUrl(urlToCheck);

    // Exact match
    if (checkUrl === currentUrl) {
        return true;
    }

    // Handle trailing slashes
    if (checkUrl === currentUrl + '/' || checkUrl + '/' === currentUrl) {
        return true;
    }

    // Check if current URL is a child of the URL to check
    // Example: checkUrl = '/admin-dashboard/roles', currentUrl = '/admin-dashboard/roles/create'
    if (currentUrl.startsWith(checkUrl + '/')) {
        return true;
    }

    return false;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
