import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

interface UseAutoRefreshOptions {
    interval?: number; // en milisegundos
    enabled?: boolean;
    preserveScroll?: boolean;
    preserveState?: boolean;
    only?: string[];
}

export function useAutoRefresh(options: UseAutoRefreshOptions = {}) {
    const {
        interval = 1000, // 1 segundo por defecto
        enabled = true,
        preserveScroll = true,
        preserveState = true,
        only = []
    } = options;

    const intervalRef = useRef<NodeJS.Timeout | null>(null);

    useEffect(() => {
        if (!enabled) return;

        const refresh = () => {
            router.reload({
                preserveScroll,
                preserveState,
                only,
                headers: {
                    'X-Refresh-Data': 'true'
                }
            });
        };

        intervalRef.current = setInterval(refresh, interval);

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, [interval, enabled, preserveScroll, preserveState, only]);

    const stopRefresh = () => {
        if (intervalRef.current) {
            clearInterval(intervalRef.current);
            intervalRef.current = null;
        }
    };

    const startRefresh = () => {
        if (!intervalRef.current && enabled) {
            intervalRef.current = setInterval(() => {
                router.reload({
                    preserveScroll,
                    preserveState,
                    only,
                    headers: {
                        'X-Refresh-Data': 'true'
                    }
                });
            }, interval);
        }
    };

    return { stopRefresh, startRefresh };
}