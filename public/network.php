<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Network Statistics</title>
   <link rel="stylesheet" href="/assets/css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
   <div class="network-container">
       <div class="device-selector">
           <select id="deviceSelect" onchange="loadStats()"></select>
       </div>
       
       <div class="stats-grid">
           <div class="stat-card">
               <canvas id="bandwidthChart"></canvas>
           </div>
           <div class="stat-card">
               <canvas id="latencyChart"></canvas>
           </div>
       </div>
       
       <div class="stats-table">
           <table>
               <thead>
                   <tr>
                       <th>Time</th>
                       <th>Upload (Mbps)</th>
                       <th>Download (Mbps)</th>
                       <th>Latency (ms)</th>
                       <th>Packet Loss (%)</th>
                   </tr>
               </thead>
               <tbody id="statsBody"></tbody>
           </table>
       </div>
   </div>

   <script>
   async function loadStats() {
       const deviceId = document.getElementById("deviceSelect").value;
       const response = await fetch(`/api/network-stats?device_id=${deviceId}`);
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
   </script>
</body>
</html>
