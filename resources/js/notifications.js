/*===========================================
  NOTIFICATIONS SYSTEM
  Simple toast notifications
===========================================*/

window.NotificationSystem = {
    show(message, type = 'info', duration = 5000) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Style the notification
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: 'white',
            padding: '16px',
            borderRadius: '8px',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
            zIndex: '10000',
            maxWidth: '400px',
            opacity: '0',
            transform: 'translateX(100%)',
            transition: 'all 0.3s ease'
        });
        
        // Add type-specific styles
        const typeColors = {
            success: '#10B981',
            error: '#EF4444', 
            warning: '#F59E0B',
            info: '#3B82F6'
        };
        
        notification.style.borderLeft = `4px solid ${typeColors[type] || typeColors.info}`;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });
        
        // Auto remove
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 8000); },
    warning(message) { this.show(message, 'warning', 6000); },
    info(message) { this.show(message, 'info'); }
};

// Global functions
window.showNotification = (msg, type) => NotificationSystem.show(msg, type);
window.showSuccess = (msg) => NotificationSystem.success(msg);
window.showError = (msg) => NotificationSystem.error(msg);

export default NotificationSystem;