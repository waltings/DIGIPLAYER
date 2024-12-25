let selectedDevices = new Set();

async function loadDevices() {
    const response = await fetch("/digiplayer/public/api/devices");
    const data = await response.json();
    document.getElementById("deviceList").innerHTML = data.devices.map(d => `
        <div class="device-item">
            <label>
                <input type="checkbox" onchange="toggleDevice(${d.id})" ${selectedDevices.has(d.id) ? "checked" : ""}>
                <span class="device-name">${d.name}</span>
                <span class="status-badge ${d.status}">${d.status}</span>
            </label>
        </div>
    `).join("");
}

function toggleDevice(id) {
    selectedDevices.has(id) ? selectedDevices.delete(id) : selectedDevices.add(id);
}

function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll(".device-item input[type=checkbox]");
    checkboxes.forEach(box => {
        box.checked = checkbox.checked;
        toggleDevice(parseInt(box.closest('.device-item').dataset.id));
    });
}

async function bulkAction(command) {
    if (selectedDevices.size === 0) {
        showNotification('Please select devices first', 'warning');
        return;
    }
    
    try {
        await fetch("/digiplayer/public/api/bulk-control", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                command,
                device_ids: Array.from(selectedDevices)
            })
        });
        showNotification(`${command} command sent to selected devices`, 'success');
    } catch (error) {
        showNotification('Failed to execute command', 'error');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

loadDevices();
setInterval(loadDevices, 30000);
