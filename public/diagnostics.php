<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Diagnostics</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="diagnostics-container">
       <div class="filters">
           <select id="deviceSelect" onchange="loadLogs()">
               <option value="">All Devices</option>
           </select>
           <select id="periodSelect" onchange="loadLogs()">
               <option value="24h">Last 24 Hours</option>
               <option value="7d">Last 7 Days</option>
               <option value="30d">Last 30 Days</option>
           </select>
           <select id="typeSelect" onchange="filterLogs()">
               <option value="">All Types</option>
               <option value="error">Errors</option>
               <option value="warning">Warnings</option>
               <option value="info">Info</option>
           </select>
       </div>
       
       <div id="logList" class="log-list"></div>
       
       <div id="systemStatus" class="status-panel"></div>
   </div>

   <script>
   async function loadLogs() {
       const deviceId = document.getElementById("deviceSelect").value;
       const period = document.getElementById("periodSelect").value;
       const response = await fetch(`/api/diagnostics?device_id=${deviceId}&period=${period}`);
       const data = await response.json();
       renderLogs(data.logs);
   }

   function renderLogs(logs) {
       const logList = document.getElementById("logList");
       logList.innerHTML = logs.map(log => `
           <div class="log-entry ${log.type}">
               <div class="log-header">
                   <span>${log.device_name}</span>
                   <span>${new Date(log.created_at).toLocaleString()}</span>
               </div>
               <div class="log-message">${log.message}</div>
               ${log.details ? `<div class="log-details">${JSON.stringify(log.details)}</div>` : ""}
           </div>
       `).join("");
   }

   loadLogs();
   </script>
</body>
</html>
