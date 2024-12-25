// Group Management Core Functionality
class GroupManager {
    constructor() {
        this.selectedGroups = new Set();
        this.init();
    }

    async init() {
        await this.loadGroups();
        await this.loadAvailableDevices();
        this.setupEventListeners();
        this.initializeDragAndDrop();
    }

    setupEventListeners() {
        // Form submissions
        const groupForm = document.getElementById('groupForm');
        if (groupForm) {
            groupForm.addEventListener('submit', (e) => this.handleGroupSubmit(e));
        }

        const permissionsForm = document.getElementById('permissionsForm');
        if (permissionsForm) {
            permissionsForm.addEventListener('submit', (e) => this.handlePermissionsSubmit(e));
        }
    }

    async loadGroups() {
        try {
            const response = await fetch('/digiplayer/public/api/groups/index.php');
            if (!response.ok) throw new Error('Failed to fetch groups');
            const data = await response.json();
            this.renderGroups(data.groups || []);
        } catch (error) {
            console.error('Error loading groups:', error);
            this.showNotification('Failed to load groups', 'error');
        }
    }

    async loadAvailableDevices() {
        try {
            const response = await fetch('/digiplayer/public/api/devices/index.php');
            if (!response.ok) throw new Error('Failed to fetch devices');
            const data = await response.json();
            this.renderAvailableDevices(data.devices || []);
        } catch (error) {
            console.error('Error loading devices:', error);
            this.showNotification('Failed to load devices', 'error');
        }
    }

    renderGroups(groups) {
        const groupList = document.getElementById('groupList');
        if (!groupList) return;

        groupList.innerHTML = groups.map(group => `
            <div class="group-card" data-group-id="${group.id}">
                <div class="group-header">
                    <h3>${this.escapeHtml(group.name)}</h3>
                    <div class="group-actions">
                        <button onclick="groupManager.editGroup(${group.id})" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="groupManager.manageGroupPermissions(${group.id})" class="btn-icon" title="Permissions">
                            <i class="fas fa-key"></i>
                        </button>
                        <button onclick="groupManager.deleteGroup(${group.id})" class="btn-icon" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="group-description">${this.escapeHtml(group.description || '')}</div>
                <div class="group-devices" ondrop="groupManager.handleDrop(event)" ondragover="groupManager.handleDragOver(event)">
                    ${this.renderGroupDevices(group.devices || [])}
                </div>
            </div>
        `).join('');
    }

    renderAvailableDevices(devices) {
        const devicesList = document.getElementById('availableDevices');
        if (!devicesList) return;

        devicesList.innerHTML = devices.map(device => `
            <div class="device-item" draggable="true" 
                ondragstart="groupManager.handleDragStart(event)" 
                data-device-id="${device.id}">
                <span class="device-name">${this.escapeHtml(device.name)}</span>
                <span class="status-badge ${device.status}">${device.status}</span>
            </div>
        `).join('');
    }

    renderGroupDevices(devices) {
        return devices.map(device => `
            <div class="device-item" draggable="true" 
                ondragstart="groupManager.handleDragStart(event)" 
                data-device-id="${device.id}">
                <span class="device-name">${this.escapeHtml(device.name)}</span>
                <span class="status-badge ${device.status}">${device.status}</span>
            </div>
        `).join('');
    }

    async editGroup(groupId) {
        try {
            const response = await fetch(`/digiplayer/public/api/groups/index.php?id=${groupId}`);
            const data = await response.json();
            
            if (!data || !data.status === 'success') {
                throw new Error('Failed to load group data');
            }
            
            const modalTitle = document.querySelector('#groupModal .modal-header h2');
            if (modalTitle) {
                modalTitle.textContent = 'Edit Group';
            }
            
            // Set form values
            document.getElementById('groupId').value = groupId;
            document.getElementById('groupName').value = data.group?.name || '';
            document.getElementById('groupDescription').value = data.group?.description || '';
            
            document.getElementById('groupModal').style.display = 'flex';
        } catch (error) {
            console.error('Error loading group details:', error);
            this.showNotification('Failed to load group details', 'error');
        }
    }

    async manageGroupPermissions(groupId) {
        try {
            const form = document.getElementById('permissionsForm');
            if (!form) {
                throw new Error('Permissions form not found');
            }
            
            const response = await fetch(`/digiplayer/public/api/groups/index.php?id=${groupId}&permissions=true`);
            const data = await response.json();
            
            if (!data || !data.status === 'success') {
                throw new Error('Failed to load permissions data');
            }
            
            // Set group ID in hidden field
            document.getElementById('permissionsGroupId').value = groupId;
            
            // Reset all checkboxes
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            
            // Set current permissions
            if (data.permissions && Array.isArray(data.permissions)) {
                data.permissions.forEach(permission => {
                    const checkbox = form.querySelector(`input[value="${permission}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            document.getElementById('permissionsModal').style.display = 'flex';
        } catch (error) {
            console.error('Error loading group permissions:', error);
            this.showNotification('Failed to load group permissions', 'error');
        }
    }

    async deleteGroup(groupId) {
        if (!confirm('Are you sure you want to delete this group?')) return;
        
        try {
            const response = await fetch('/digiplayer/public/api/groups/index.php', {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: groupId })
            });

            if (!response.ok) throw new Error('Failed to delete group');

            await this.loadGroups();
            this.showNotification('Group deleted successfully', 'success');
        } catch (error) {
            console.error('Error deleting group:', error);
            this.showNotification('Failed to delete group', 'error');
        }
    }

    // Drag and Drop Handlers
    handleDragStart(event) {
        event.dataTransfer.setData('deviceId', event.target.dataset.deviceId);
    }

    handleDragOver(event) {
        event.preventDefault();
        event.currentTarget.classList.add('dragover');
    }

    handleDragLeave(event) {
        event.currentTarget.classList.remove('dragover');
    }

    async handleDrop(event) {
        event.preventDefault();
        const deviceId = event.dataTransfer.getData('deviceId');
        const groupId = event.currentTarget.closest('.group-card').dataset.groupId;
        
        try {
            const response = await fetch('/digiplayer/public/api/device-group/index.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    device_id: deviceId,
                    group_id: groupId
                })
            });

            if (!response.ok) throw new Error('Failed to update device group');

            await Promise.all([
                this.loadGroups(),
                this.loadAvailableDevices()
            ]);

            this.showNotification('Device assigned successfully', 'success');
        } catch (error) {
            console.error('Error updating device group:', error);
            this.showNotification('Failed to assign device to group', 'error');
        }
    }

    // Form Handlers
    async handleGroupSubmit(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const groupId = document.getElementById('groupId')?.value;

        try {
            const response = await fetch('/digiplayer/public/api/groups/index.php', {
                method: groupId ? 'PUT' : 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    id: groupId || undefined,
                    name: formData.get('name'),
                    description: formData.get('description')
                })
            });

            if (!response.ok) throw new Error('Failed to save group');

            await this.loadGroups();
            this.closeModal('groupModal');
            this.showNotification(
                groupId ? 'Group updated successfully' : 'Group created successfully',
                'success'
            );
            event.target.reset();
        } catch (error) {
            console.error('Error saving group:', error);
            this.showNotification('Failed to save group', 'error');
        }
    }

    async handlePermissionsSubmit(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const groupId = document.getElementById('permissionsGroupId')?.value;

        try {
            const response = await fetch('/digiplayer/public/api/groups/index.php', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    id: groupId,
                    permissions: Array.from(formData.getAll('permissions[]'))
                })
            });

            if (!response.ok) throw new Error('Failed to update permissions');

            this.closeModal('permissionsModal');
            this.showNotification('Permissions updated successfully', 'success');
        } catch (error) {
            console.error('Error updating permissions:', error);
            this.showNotification('Failed to update permissions', 'error');
        }
    }

    // Utility Methods
    showNotification(message, type = 'info') {
        // You can implement your preferred notification system here
        alert(message);
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// Initialize Group Manager
const groupManager = new GroupManager();