class DashboardWidgets {
    constructor() {
        this.widgets = new Map();
        this.init();
    }

    init() {
        this.setupWidgets();
        this.loadData();
    }

    setupWidgets() {
        this.addWidget('deviceStatus', {
            title: 'Device Status',
            updateInterval: 30000,
            async getData() {
                const response = await fetch('/digiplayer/public/api/devices/status');
                return await response.json();
            },
            render(data) {
                return `
                    <div class="widget-content">
                        <div class="status-count">
                            <div class="online">${data.online} Online</div>
                            <div class="offline">${data.offline} Offline</div>
                        </div>
                    </div>
                `;
            }
        });

        this.addWidget('storageUsage', {
            title: 'Storage Usage',
            updateInterval: 60000,
            async getData() {
                const response = await fetch('/digiplayer/public/api/storage/status');
                return await response.json();
            },
            render(data) {
                return `
                    <div class="widget-content">
                        <div class="progress-bar">
                            <div class="progress" style="width: ${data.usagePercentage}%"></div>
                        </div>
                        <div class="usage-text">${this.formatBytes(data.used)} / ${this.formatBytes(data.total)}</div>
                    </div>
                `;
            }
        });
    }

    formatBytes(bytes) {
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (bytes === 0) return '0 B';
        const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i)) + ' ' + sizes[i];
    }
}

new DashboardWidgets();
