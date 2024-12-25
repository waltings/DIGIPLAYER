class DeviceSyncManager {
    constructor() {
        this.syncInterval = 30000;
        this.syncQueue = new Map();
        this.init();
    }

    async init() {
        this.startSync();
    }
    // Rest of implementation
}
