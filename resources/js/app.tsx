import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import Home from './Pages/Home';
import Lebenslauf from './Pages/Lebenslauf';
import ProjectShow from './Pages/ProjectShow';

/*
 | Every page component is registered eagerly.
 |
 | Three pages do not justify code splitting: the split would add a second
 | request before anything renders, and the whole bundle gzips to a fraction of
 | what a single model response costs.
 */
createInertiaApp({
    resolve: (name) => {
        const pages = { Home, ProjectShow, Lebenslauf };

        const page = pages[name as keyof typeof pages];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        return page;
    },

    setup({ el, App, props }) {
        const container = el ?? document.getElementById('app');

        if (container === null) {
            throw new Error('Inertia mount point not found.');
        }

        createRoot(container).render(<App {...props} />);
    },
});