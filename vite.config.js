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
