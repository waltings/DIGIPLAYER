class SystemSettings {
    constructor() {
        this.init();
    }

    async init() {
        await this.loadSettings();
        this.setupEventListeners();
    }

    async loadSettings() {
        const response = await fetch('/digiplayer/public/api/settings');
        this.settings = await response.json();
        this.renderSettings();
    }

    renderSettings() {
        const container = document.getElementById('settingsContainer');
        container.innerHTML = `
            <div class="settings-group">
                <h3>General Settings</h3>
                ${this.renderGeneralSettings()}
            </div>
            <div class="settings-group">
                <h3>Network Settings</h3>
                ${this.renderNetworkSettings()}
            </div>
            <div class="settings-group">
                <h3>Storage Settings</h3>
                ${this.renderStorageSettings()}
            </div>
        `;
    }

    async saveSettings(section, data) {
        await fetch('/digiplayer/public/api/settings', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ section, data })
        });
    }
}

new SystemSettings();
