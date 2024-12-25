class GroupPermissions {
    constructor() {
        this.init();
    }

    async init() {
        this.loadPermissions();
        this.setupEventListeners();
    }

    async loadPermissions() {
        const response = await fetch('/digiplayer/public/api/groups/permissions');
        const data = await response.json();
        this.renderPermissions(data.permissions);
    }

    renderPermissions(permissions) {
        const container = document.getElementById('groupPermissions');
        container.innerHTML = `
            <div class="permissions-grid">
                ${this.renderPermissionItems(permissions)}
            </div>
        `;
    }

    renderPermissionItems(permissions) {
        return Object.entries(permissions).map(([key, value]) => `
            <div class="permission-item">
                <label>
                    <input type="checkbox" 
                           value="${key}" 
                           ${value ? 'checked' : ''}
                           onchange="groupPermissions.updatePermission('${key}', this.checked)">
                    ${this.formatPermissionLabel(key)}
                </label>
            </div>
        `).join('');
    }

    async updatePermission(key, value) {
        try {
            await fetch('/digiplayer/public/api/groups/permissions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    permission: key,
                    value: value
                })
            });
            showNotification('Permission updated successfully', 'success');
        } catch (error) {
            console.error('Failed to update permission:', error);
            showNotification('Failed to update permission', 'error');
        }
    }
}

const groupPermissions = new GroupPermissions();
