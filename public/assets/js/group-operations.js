class GroupOperations {
    constructor() {
        this.init();
    }

    init() {
        this.initDragAndDrop();
        this.loadGroupSettings();
    }

    async loadGroupSettings() {
        try {
            const response = await fetch('/digiplayer/public/api/groups/settings');
            const data = await response.json();
            this.applyGroupSettings(data.settings);
        } catch (error) {
            console.error('Failed to load group settings:', error);
        }
    }

    initDragAndDrop() {
        const containers = document.querySelectorAll('.group-devices');
        containers.forEach(container => {
            new Sortable(container, {
                group: 'shared',
                animation: 150,
                onEnd: (evt) => this.handleDeviceMove(evt)
            });
        });
    }

    async handleDeviceMove(evt) {
        const deviceId = evt.item.dataset.deviceId;
        const targetGroupId = evt.to.dataset.groupId;
        const sourceGroupId = evt.from.dataset.groupId;

        try {
            await fetch('/digiplayer/public/api/groups/move-device', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    device_id: deviceId,
                    target_group_id: targetGroupId,
                    source_group_id: sourceGroupId
                })
            });
        } catch (error) {
            console.error('Failed to move device:', error);
            // Revert the move
            evt.from.appendChild(evt.item);
        }
    }

    // Schedule Management for Groups
    async scheduleGroupContent() {
        const groupId = this.selectedGroup;
        const schedule = {
            playlist_id: document.getElementById('playlistSelect').value,
            start_time: document.getElementById('startTime').value,
            end_time: document.getElementById('endTime').value,
            days: Array.from(document.querySelectorAll('.day-select:checked')).map(cb => cb.value)
        };

        try {
            await fetch('/digiplayer/public/api/groups/schedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    group_id: groupId,
                    schedule: schedule
                })
            });
            showNotification('Schedule updated successfully', 'success');
        } catch (error) {
            console.error('Failed to schedule group content:', error);
            showNotification('Failed to update schedule', 'error');
        }
    }

    // Group Monitoring
    async updateGroupStatus() {
        try {
            const response = await fetch('/digiplayer/public/api/groups/status');
            const data = await response.json();
            this.updateGroupStatusUI(data);
        } catch (error) {
            console.error('Failed to update group status:', error);
        }
    }

    updateGroupStatusUI(data) {
        document.getElementById('groupStatusContainer').innerHTML = data.groups.map(group => `
            <div class="group-status-card">
                <h3>${group.name}</h3>
                <div class="status-grid">
                    <div class="status-item">
                        <label>Online Devices</label>
                        <span>${group.online_devices}/${group.total_devices}</span>
                    </div>
                    <div class="status-item">
                        <label>Current Playlist</label>
                        <span>${group.current_playlist || 'None'}</span>
                    </div>
                    <div class="status-item">
                        <label>Last Update</label>
                        <span>${new Date(group.last_update).toLocaleString()}</span>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

// Initialize group operations
const groupOps = new GroupOperations();
setInterval(() => groupOps.updateGroupStatus(), 30000);
