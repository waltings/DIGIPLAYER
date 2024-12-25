<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>DigiPlayer - Analytics</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="analytics-container">
        <div class="filters">
            <select id="deviceSelect">
                <option value="">All Devices</option>
            </select>
            <select id="periodSelect">
                <option value="day">Last 24 Hours</option>
                <option value="week">Last Week</option>
                <option value="month">Last Month</option>
            </select>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>Content Views</h3>
                <canvas id="viewsChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Playback Time</h3>
                <canvas id="durationChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Error Rate</h3>
                <canvas id="errorChart"></canvas>
            </div>
        </div>

        <div class="stats-table">
            <table>
                <thead>
                    <tr>
                        <th>Content</th>
                        <th>Views</th>
                        <th>Duration</th>
                        <th>Completion Rate</th>
                        <th>Errors</th>
                    </tr>
                </thead>
                <tbody id="statsBody"></tbody>
            </table>
        </div>
    </div>

    <script>
    async function loadAnalytics() {
        const deviceId = document.getElementById("deviceSelect").value;
        const period = document.getElementById("periodSelect").value;
        const response = await fetch(`/api/analytics?device_id=${deviceId}&period=${period}`);
        const data = await response.json();
        
        updateCharts(data.analytics);
        updateTable(data.analytics);
    }

    loadAnalytics();
    </script>
</body>
</html>
