function updateAlerts() {
    fetch("/digiplayer/public/api/notifications")
        .then(r => r.json())
        .then(data => {
            document.getElementById("activeAlerts").innerHTML = data.notifications
                .map(n => `
                    <div class="alert-card severity-${n.severity}">
                        <div class="alert-header">
                            <span class="device-name">${n.device_name}</span>
                            <span class="alert-time">${formatTime(n.created_at)}</span>
                        </div>
                        <div class="alert-message">${n.message}</div>
                        <div class="alert-actions">
                            ${n.status === 'active' ? 
                                `<button onclick="acknowledgeAlert(${n.id})" class="btn-ack">Acknowledge</button>` : 
                                `<span class="ack-by">Acknowledged by: ${n.acknowledged_by_name}</span>`
                            }
                        </div>
                    </div>
                `).join("");
        });
}

async function acknowledgeAlert(id) {
    try {
        await fetch("/digiplayer/public/api/notifications", {
            method: "PUT",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                action: "acknowledge",
                alert_id: id
            })
        });
        updateAlerts();
    } catch (error) {
        console.error('Failed to acknowledge alert:', error);
    }
}

function formatTime(timestamp) {
    return new Date(timestamp).toLocaleString();
}

updateAlerts();
setInterval(updateAlerts, 30000);
