import '../css/app.css';
// import './echo'; // Deshabilitado temporalmente

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { Toaster } from '@/components/ui/sonner';
import { route } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        // Configurar Ziggy globalmente
        // @ts-expect-error
        window.route = (name, params, absolute) => {
            return route(name, params, absolute, {
                // @ts-expect-error
                ...props.initialPage.props.ziggy,
                // @ts-expect-error
                location: new URL(props.initialPage.props.ziggy.location),
            });
        };

        root.render(
            <>
                <App {...props} />
                <Toaster
                    position="top-right"
                    toastOptions={{
                        style: {
                            background: 'white',
                            color: 'black',
                            border: '1px solid #e5e7eb',
                            fontSize: '14px',
                        },
                        className: 'toast-custom',
                        descriptionClassName: 'toast-description',
                    }}
                />
            </>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
