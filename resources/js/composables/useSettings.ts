// composables/useSettings.ts
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Composable for accessing application settings
 * Mirrors the Laravel setting() helper function
 */
export function useSettings() {
    const page = usePage();

    // All settings as reactive computed property
    const settings = computed(() => {
        return (page.props.settings as Record<string, any>) || {};
    });

    /**
     * Get a setting value by key - matches Laravel's setting() helper
     * @param key - Setting key
     * @param defaultValue - Default value if setting not found
     */
    const setting = (key: string, defaultValue: any = null): any => {
        return settings.value[key] ?? defaultValue;
    };

    /**
     * Get multiple settings at once
     * @param keys - Array of setting keys
     */
    const getMultiple = (keys: string[]): Record<string, any> => {
        return keys.reduce(
            (acc, key) => {
                acc[key] = settings.value[key] ?? null;
                return acc;
            },
            {} as Record<string, any>,
        );
    };

    /**
     * Check if a setting exists and has truthy value
     * Handles string '1', 'true', boolean true, etc.
     */
    const isEnabled = (key: string): boolean => {
        const value = settings.value[key];
        return (
            value === '1' || value === 'true' || value === true || value === 1
        );
    };

    /**
     * Check if a setting exists
     */
    const has = (key: string): boolean => {
        return key in settings.value;
    };

    return {
        // Reactive settings object
        settings,

        // Helper functions
        setting,
        getMultiple,
        isEnabled,
        has,
    };
}
