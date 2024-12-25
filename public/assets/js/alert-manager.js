class AlertManager {
    constructor() {
        this.checkInterval = 10000;
        this.activeAlerts = new Map();
        this.init();
    }

    async init() {
        await this.checkAlerts();
        setInterval(() => this.checkAlerts(), this.checkInterval);
    }

    async checkAlerts() {
        try {
            const response = await fetch('/digiplayer/public/api/monitoring/alerts');
            const alerts = await response.json();
            this.processAlerts(alerts);
        } catch (error) {
            console.error('Failed to check alerts:', error);
        }
    }

    processAlerts(alerts) {
        alerts.forEach(alert => this.handleAlert(alert));
    }

    handleAlert(alert) {
        if (!this.activeAlerts.has(alert.id)) {
            this.showNotification(alert);
            this.activeAlerts.set(alert.id, alert);
        }
    }

    showNotification(alert) {
        // Notification implementation
    }
}

new AlertManager();
