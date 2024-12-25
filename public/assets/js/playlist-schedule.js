class PlaylistScheduler {
    constructor() {
        this.scheduleGrid = document.getElementById('scheduleGrid');
        this.init();
    }

    init() {
        this.loadSchedules();
        this.setupEventListeners();
    }

    async loadSchedules() {
        const response = await fetch('/digiplayer/public/api/schedules');
        const data = await response.json();
        this.renderSchedules(data.schedules);
    }

    // Rest of implementation
}
