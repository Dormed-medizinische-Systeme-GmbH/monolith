import { createInertiaApp } from '@inertiajs/svelte';
import AppLayout from '@/layouts/AppLayout.svelte';
import AuthLayout from '@/layouts/AuthLayout.svelte';
import ErpLayout from '@/layouts/ErpLayout.svelte';
import { initializeFlashToast } from '@/lib/flash-toast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            // Login-Seiten bringen ihr eigenes ganzseitiges Layout mit — die
            // App-Shell mit Sidebar waere davor sinnlos.
            case name.endsWith('/Login'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            // Das ERP bringt seine eigene Huelle mit (Seitenleiste, Kopfzeile).
            // Portal und Shop bekommen spaeter eine eigene; bis dahin genuegt
            // ihnen der schlichte Rahmen.
            case name.startsWith('erp/'):
                return ErpLayout;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...

// This will listen for flash toast data from the server...
initializeFlashToast();
