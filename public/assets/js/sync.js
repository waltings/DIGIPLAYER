class ContentSync {
    constructor() {
        this.syncInterval = 30000;
        this.connections = new Map();
    }

    initDeviceSync(deviceId) {
        if (this.connections.has(deviceId)) return;
        
        const ws = new WebSocket(`ws://${window.location.hostname}/ws`);
        
        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleSyncEvent(data);
        };
        
        this.connections.set(deviceId, ws);
    }

    handleSyncEvent(data) {
        switch(data.type) {
            case 'media_update':
                loadMedia();
                break;
            case 'playlist_update':
                loadPlaylists();
                break;
            case 'device_update':
                loadDevices();
                break;
        }
    }
}

const sync = new ContentSync();
