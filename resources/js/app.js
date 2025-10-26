import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import real-time dashboard and charts (only if on relevant pages)
if (document.querySelector('[data-dashboard-live]') || document.querySelector('[data-chart]')) {
    import('./dashboard');
}

if (document.querySelector('[data-chart-type]')) {
    import('./charts');
}

// Import Echo for broadcasting (if available)
if (import.meta.env.VITE_BROADCAST_DRIVER === 'pusher') {
    import('./echo');
}
