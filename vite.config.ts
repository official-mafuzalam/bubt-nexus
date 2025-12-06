@import 'tailwindcss';

@import 'tw-animate-css';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';

@custom-variant dark (&:is(.dark *));

/* Import the color palette */
@import 'tailwindcss/colors';

@theme inline {
    --font-sans:
        Instrument Sans, ui-sans-serif, system-ui, sans-serif,
        'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol',
        'Noto Color Emoji';

    --radius-lg: var(--radius);
    --radius-md: calc(var(--radius) - 2px);
    --radius-sm: calc(var(--radius) - 4px);

    --color-background: var(--background);
    --color-foreground: var(--foreground);

    --color-card: var(--card);
    --color-card-foreground: var(--card-foreground);

    --color-popover: var(--popover);
    --color-popover-foreground: var(--popover-foreground);

    --color-primary: var(--primary);
    --color-primary-foreground: var(--primary-foreground);

    --color-secondary: var(--secondary);
    --color-secondary-foreground: var(--secondary-foreground);

    --color-muted: var(--muted);
    --color-muted-foreground: var(--muted-foreground);

    --color-accent: var(--accent);
    --color-accent-foreground: var(--accent-foreground);

    --color-destructive: var(--destructive);
    --color-destructive-foreground: var(--destructive-foreground);

    --color-border: var(--border);
    --color-input: var(--input);
    --color-ring: var(--ring);

    --color-chart-1: var(--chart-1);
    --color-chart-2: var(--chart-2);
    --color-chart-3: var(--chart-3);
    --color-chart-4: var(--chart-4);
    --color-chart-5: var(--chart-5);

    --color-sidebar: var(--sidebar-background);
    --color-sidebar-foreground: var(--sidebar-foreground);
    --color-sidebar-primary: var(--sidebar-primary);
    --color-sidebar-primary-foreground: var(--sidebar-primary-foreground);
    --color-sidebar-accent: var(--sidebar-accent);
    --color-sidebar-accent-foreground: var(--sidebar-accent-foreground);
    --color-sidebar-border: var(--sidebar-border);
    --color-sidebar-ring: var(--sidebar-ring);

    /* Add color definitions for blue shades */
    --color-blue-50: #eff6ff;
    --color-blue-100: #dbeafe;
    --color-blue-200: #bfdbfe;
    --color-blue-300: #93c5fd;
    --color-blue-400: #60a5fa;
    --color-blue-500: #3b82f6;
    --color-blue-600: #2563eb;
    --color-blue-700: #1d4ed8;
    --color-blue-800: #1e40af;
    --color-blue-900: #1e3a8a;
    --color-blue-950: #172554;

    /* Add purple shades for faculty section */
    --color-purple-50: #faf5ff;
    --color-purple-100: #f3e8ff;
    --color-purple-200: #e9d5ff;
    --color-purple-300: #d8b4fe;
    --color-purple-400: #c084fc;
    --color-purple-500: #a855f7;
    --color-purple-600: #9333ea;
    --color-purple-700: #7e22ce;
    --color-purple-800: #6b21a8;
    --color-purple-900: #581c87;
    --color-purple-950: #3b0764;

    /* Add other color shades you might need */
    --color-gray-50: #f9fafb;
    --color-gray-100: #f3f4f6;
    --color-gray-200: #e5e7eb;
    --color-gray-300: #d1d5db;
    --color-gray-400: #9ca3af;
    --color-gray-500: #6b7280;
    --color-gray-600: #4b5563;
    --color-gray-700: #374151;
    --color-gray-800: #1f2937;
    --color-gray-900: #111827;
    --color-gray-950: #030712;

    --color-green-500: #10b981;
    --color-yellow-500: #f59e0b;
    --color-orange-500: #f97316;
    --color-red-500: #ef4444;
}

/*
  The default border color has changed to `currentColor` in Tailwind CSS v4,
  so we've added these compatibility styles to make sure everything still
  looks the same as it did with Tailwind CSS v3.

  If we ever want to remove these styles, we need to add an explicit border
  color utility to any element that depends on these defaults.
*/
@layer base {
    *,
    ::after,
    ::before,
    ::backdrop,
    ::file-selector-button {
        border-color: var(--color-gray-200, currentColor);
    }
}

@layer utilities {
    body,
    html {
        --font-sans:
            'Instrument Sans', ui-sans-serif, system-ui, sans-serif,
            'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol',
            'Noto Color Emoji';
    }
}

:root {
    --background: hsl(0 0% 100%);
    --foreground: hsl(0 0% 3.9%);
    --card: hsl(0 0% 100%);
    --card-foreground: hsl(0 0% 3.9%);
    --popover: hsl(0 0% 100%);
    --popover-foreground: hsl(0 0% 3.9%);
    --primary: hsl(0 0% 9%);
    --primary-foreground: hsl(0 0% 98%);
    --secondary: hsl(0 0% 92.1%);
    --secondary-foreground: hsl(0 0% 9%);
    --muted: hsl(0 0% 96.1%);
    --muted-foreground: hsl(0 0% 45.1%);
    --accent: hsl(0 0% 96.1%);
    --accent-foreground: hsl(0 0% 9%);
    --destructive: hsl(0 84.2% 60.2%);
    --destructive-foreground: hsl(0 0% 98%);
    --border: hsl(0 0% 92.8%);
    --input: hsl(0 0% 89.8%);
    --ring: hsl(0 0% 3.9%);
    --chart-1: hsl(12 76% 61%);
    --chart-2: hsl(173 58% 39%);
    --chart-3: hsl(197 37% 24%);
    --chart-4: hsl(43 74% 66%);
    --chart-5: hsl(27 87% 67%);
    --radius: 0.5rem;
    --sidebar-background: hsl(0 0% 98%);
    --sidebar-foreground: hsl(240 5.3% 26.1%);
    --sidebar-primary: hsl(0 0% 10%);
    --sidebar-primary-foreground: hsl(0 0% 98%);
    --sidebar-accent: hsl(0 0% 94%);
    --sidebar-accent-foreground: hsl(0 0% 30%);
    --sidebar-border: hsl(0 0% 91%);
    --sidebar-ring: hsl(217.2 91.2% 59.8%);
    --sidebar: hsl(0 0% 98%);
}

.dark {
    --background: hsl(0 0% 3.9%);
    --foreground: hsl(0 0% 98%);
    --card: hsl(0 0% 3.9%);
    --card-foreground: hsl(0 0% 98%);
    --popover: hsl(0 0% 3.9%);
    --popover-foreground: hsl(0 0% 98%);
    --primary: hsl(0 0% 98%);
    --primary-foreground: hsl(0 0% 9%);
    --secondary: hsl(0 0% 14.9%);
    --secondary-foreground: hsl(0 0% 98%);
    --muted: hsl(0 0% 16.08%);
    --muted-foreground: hsl(0 0% 63.9%);
    --accent: hsl(0 0% 14.9%);
    --accent-foreground: hsl(0 0% 98%);
    --destructive: hsl(0 84% 60%);
    --destructive-foreground: hsl(0 0% 98%);
    --border: hsl(0 0% 14.9%);
    --input: hsl(0 0% 14.9%);
    --ring: hsl(0 0% 83.1%);
    --chart-1: hsl(220 70% 50%);
    --chart-2: hsl(160 60% 45%);
    --chart-3: hsl(30 80% 55%);
    --chart-4: hsl(280 65% 60%);
    --chart-5: hsl(340 75% 55%);
    --sidebar-background: hsl(0 0% 7%);
    --sidebar-foreground: hsl(0 0% 95.9%);
    --sidebar-primary: hsl(360, 100%, 100%);
    --sidebar-primary-foreground: hsl(0 0% 100%);
    --sidebar-accent: hsl(0 0% 15.9%);
    --sidebar-accent-foreground: hsl(240 4.8% 95.9%);
    --sidebar-border: hsl(0 0% 15.9%);
    --sidebar-ring: hsl(217.2 91.2% 59.8%);
    --sidebar: hsl(240 5.9% 10%);
}

@layer base {
    * {
        @apply border-border outline-ring/50;
    }
    body {
        @apply bg-background text-foreground;
    }
}

/* Add utility definitions for blue colors */
@utility text-blue-600 {
    color: var(--color-blue-600);
}

@utility border-blue-600 {
    border-color: var(--color-blue-600);
}

@utility bg-blue-600 {
    background-color: var(--color-blue-600);
}

@utility text-blue-500 {
    color: var(--color-blue-500);
}

@utility bg-blue-500 {
    background-color: var(--color-blue-500);
}

@utility bg-blue-50 {
    background-color: var(--color-blue-50);
}

@utility border-blue-100 {
    border-color: var(--color-blue-100);
}

@utility bg-blue-100 {
    background-color: var(--color-blue-100);
}

@utility text-blue-400 {
    color: var(--color-blue-400);
}

/* Add utility definitions for purple colors */
@utility text-purple-600 {
    color: var(--color-purple-600);
}

@utility border-purple-600 {
    border-color: var(--color-purple-600);
}

@utility bg-purple-600 {
    background-color: var(--color-purple-600);
}

@utility text-purple-500 {
    color: var(--color-purple-500);
}

@utility bg-purple-500 {
    background-color: var(--color-purple-500);
}

@utility bg-purple-50 {
    background-color: var(--color-purple-50);
}

@utility border-purple-100 {
    border-color: var(--color-purple-100);
}

@utility bg-purple-100 {
    background-color: var(--color-purple-100);
}

@utility text-purple-400 {
    color: var(--color-purple-400);
}

/* Add utility definitions for gray colors */
@utility text-gray-500 {
    color: var(--color-gray-500);
}

@utility text-gray-600 {
    color: var(--color-gray-600);
}

@utility text-gray-700 {
    color: var(--color-gray-700);
}

@utility text-gray-900 {
    color: var(--color-gray-900);
}

@utility bg-gray-50 {
    background-color: var(--color-gray-50);
}

@utility border-gray-200 {
    border-color: var(--color-gray-200);
}

@utility hover\:text-gray-700:hover {
    color: var(--color-gray-700);
}

/* Add other color utilities */
@utility text-green-500 {
    color: var(--color-green-500);
}

@utility text-yellow-500 {
    color: var(--color-yellow-500);
}

@utility text-orange-500 {
    color: var(--color-orange-500);
}

@utility bg-green-500 {
    background-color: var(--color-green-500);
}

@utility bg-yellow-500 {
    background-color: var(--color-yellow-500);
}

@utility bg-orange-500 {
    background-color: var(--color-orange-500);
}

/* Dark mode variants */
@utility dark\:text-blue-500:is(.dark *) {
    color: var(--color-blue-500);
}

@utility dark\:border-blue-500:is(.dark *) {
    border-color: var(--color-blue-500);
}

@utility dark\:bg-blue-900\/10:is(.dark *) {
    background-color: color-mix(in srgb, var(--color-blue-900) 10%, transparent);
}

@utility dark\:bg-blue-900\/30:is(.dark *) {
    background-color: color-mix(in srgb, var(--color-blue-900) 30%, transparent);
}

@utility dark\:text-blue-400:is(.dark *) {
    color: var(--color-blue-400);
}

@utility dark\:text-gray-100:is(.dark *) {
    color: var(--color-gray-100);
}

@utility dark\:text-gray-300:is(.dark *) {
    color: var(--color-gray-300);
}

@utility dark\:text-gray-400:is(.dark *) {
    color: var(--color-gray-400);
}

@utility dark\:bg-gray-800:is(.dark *) {
    background-color: var(--color-gray-800);
}

@utility dark\:border-gray-700:is(.dark *) {
    border-color: var(--color-gray-700);
}

@utility dark\:hover\:text-gray-300:hover:is(.dark *) {
    color: var(--color-gray-300);
}