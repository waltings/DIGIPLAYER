class PerformanceMonitor {
    constructor() {
        this.monitorInterval = 60000;
        this.metrics = {
            cpu: [],
            memory: [],
            network: []
        };
        this.init();
    }

    async init() {
        await this.fetchMetrics();
        setInterval(() => this.fetchMetrics(), this.monitorInterval);
    }

    async fetchMetrics() {
        try {
            const response = await fetch('/digiplayer/public/api/monitoring/system-metrics');
            const data = await response.json();
            this.updateCharts(data);
        } catch (error) {
            console.error('Failed to fetch metrics:', error);
        }
    }

    updateCharts(data) {
        this.updateCPUChart(data.cpu);
        this.updateMemoryChart(data.memory);
        this.updateNetworkChart(data.network);
    }

    updateCPUChart(data) {
        // Chart implementation
    }

    updateMemoryChart(data) {
        // Chart implementation
    }

    updateNetworkChart(data) {
        // Chart implementation
    }
}

new PerformanceMonitor();
