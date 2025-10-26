/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Check if environment variables are available
const broadcastDriver = import.meta.env.VITE_BROADCAST_DRIVER || 'pusher';
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY || 'f11dc48551a0d1842558';
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1';

console.log('Echo config:', { broadcastDriver, pusherKey, pusherCluster });

// Only initialize if broadcasting is enabled
if (broadcastDriver === 'pusher' && pusherKey) {
    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true,
            encrypted: true,
            enabledTransports: ['ws', 'wss'],
        });

        // Log connection status
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ Connected to Pusher');
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ Pusher connection error:', err);
        });
        
        console.log('✅ Echo initialized successfully');
    } catch (error) {
        console.error('❌ Failed to initialize Echo:', error);
        window.Echo = null;
    }
} else {
    console.log('⚠️ Broadcasting is not enabled. Using polling fallback.');
    window.Echo = null;
}

export default window.Echo;

