async function loadStats() {
    const deviceId = document.getElementById("deviceSelect").value;
    const response = await fetch(`/digiplayer/public/api/network-stats?device_id=${deviceId}`);
    const data = await response.json();
    
    updateCharts(data.stats);
    updateTable(data.stats);
}

function updateCharts(stats) {
    const ctx = document.getElementById("bandwidthChart").getContext("2d");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: stats.map(s => new Date(s.recorded_at).toLocaleTimeString()),
            datasets: [{
                label: "Upload",
                data: stats.map(s => s.bandwidth_up),
                borderColor: "#007bff"
            }, {
                label: "Download",
                data: stats.map(s => s.bandwidth_down),
                borderColor: "#28a745"
            }]
        }
    });

    const latencyCtx = document.getElementById("latencyChart").getContext("2d");
    new Chart(latencyCtx, {
        type: "line",
        data: {
            labels: stats.map(s => new Date(s.recorded_at).toLocaleTimeString()),
            datasets: [{
                label: "Latency",
                data: stats.map(s => s.latency),
                borderColor: "#dc3545"
            }]
        }
    });
}

function updateTable(stats) {
    document.getElementById("statsBody").innerHTML = stats.map(s => `
        <tr>
            <td>${new Date(s.recorded_at).toLocaleString()}</td>
            <td>${s.bandwidth_up.toFixed(2)}</td>
            <td>${s.bandwidth_down.toFixed(2)}</td>
            <td>${s.latency}</td>
            <td>${s.packet_loss.toFixed(2)}</td>
        </tr>
    `).join("");
}

loadStats();
setInterval(loadStats, 30000);
