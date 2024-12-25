async function loadLogs() {
    const deviceId = document.getElementById("deviceSelect").value;
    const period = document.getElementById("periodSelect").value;
    const type = document.getElementById("typeSelect").value;

    try {
        const response = await fetch(`/digiplayer/public/api/diagnostics?device_id=${deviceId}&period=${period}`);
        const data = await response.json();
        renderLogs(data.logs.filter(log => !type || log.type === type));
    } catch (error) {
        console.error('Failed to load logs:', error);
    }
}

function renderLogs(logs) {
    document.getElementById("logList").innerHTML = logs.map(log => `
        <div class="log-entry ${log.type}">
            <div class="log-header">
                <span class="device">${log.device_name}</span>
                <span class="timestamp">${new Date(log.created_at).toLocaleString()}</span>
            </div>
            <div class="log-message">${log.message}</div>
            ${log.details ? `<pre class="log-details">${JSON.stringify(log.details, null, 2)}</pre>` : ''}
        </div>
    `).join("");
}

function updateSystemStatus() {
    fetch("/digiplayer/public/api/diagnostics/system")
        .then(r => r.json())
        .then(data => {
            document.getElementById("systemStatus").innerHTML = `
                <div class="status-grid">
                    <div class="status-item">
                        <label>CPU Usage</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${data.cpu_usage}%"></div>
                        </div>
                    </div>
                    <div class="status-item">
                        <label>Memory Usage</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${data.memory_usage}%"></div>
                        </div>
                    </div>
                    <div class="status-item">
                        <label>Disk Usage</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${data.disk_usage}%"></div>
                        </div>
                    </div>
                </div>
            `;
        });
}

loadLogs();
updateSystemStatus();
setInterval(updateSystemStatus, 60000);

document.getElementById("deviceSelect").addEventListener("change", loadLogs);
document.getElementById("periodSelect").addEventListener("change", loadLogs);
document.getElementById("typeSelect").addEventListener("change", loadLogs);
