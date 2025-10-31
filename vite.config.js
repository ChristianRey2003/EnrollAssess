import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Main application assets
                'resources/css/app.css',
                'resources/js/app.js',
                
                // Admin-specific bundles
                'resources/css/admin.css',
                'resources/js/admin.js',
                
                // Component-specific assets
                'resources/css/components.css',
                
                // Exam interface (separate bundle for performance)
                'resources/css/exam.css',
                'resources/js/exam.js'
            ],
            refresh: true,
        }),
    ],
    
    // Define environment variables for frontend
    define: {
        'import.meta.env.VITE_BROADCAST_DRIVER': JSON.stringify(process.env.VITE_BROADCAST_DRIVER || process.env.BROADCAST_DRIVER || process.env.BROADCAST_CONNECTION || 'pusher'),
        'import.meta.env.VITE_PUSHER_APP_KEY': JSON.stringify(process.env.VITE_PUSHER_APP_KEY || process.env.PUSHER_APP_KEY || 'f11dc48551a0d1842558'),
        'import.meta.env.VITE_PUSHER_APP_CLUSTER': JSON.stringify(process.env.VITE_PUSHER_APP_CLUSTER || process.env.PUSHER_APP_CLUSTER || 'ap1'),
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor libraries
                    vendor: ['alpinejs'],
                    
                    // Admin functionality
                    admin: [
                        'resources/js/utils/modal-manager.js',
                        'resources/js/utils/form-validator.js',
                        'resources/js/notifications.js'
                    ]
                }
            }
        },
        // Enable CSS code splitting
        cssCodeSplit: true,
        
        // Optimize assets
        assetsInlineLimit: 4096, // Inline assets smaller than 4kb
        
        // Enable source maps for development
        sourcemap: process.env.NODE_ENV === 'development'
    },
    
    // Optimize dependencies
    optimizeDeps: {
        include: ['alpinejs']
    }
});
