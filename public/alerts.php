<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Alerts</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <nav>
       <a href="/dashboard.php">Dashboard</a>
       <a href="/alerts.php">Alerts</a>
   </nav>

   <div class="alerts-container">
       <div class="alerts-header">
           <h1>System Alerts</h1>
           <div class="alert-filters">
               <select id="severityFilter">
                   <option value="">All Severities</option>
                   <option value="1">Low</option>
                   <option value="2">Medium</option>
                   <option value="3">High</option>
               </select>
           </div>
       </div>

       <div id="activeAlerts"></div>

       <script>
       function updateAlerts() {
           fetch("/api/notifications")
               .then(r => r.json())
               .then(data => {
                   document.getElementById("activeAlerts").innerHTML = data.notifications
                       .map(n => `
                           <div class="alert-card severity-${n.severity}">
                               <div class="alert-header">
                                   <span class="device-name">${n.device_name}</span>
                                   <span class="alert-time">${n.created_at}</span>
                               </div>
                               <div class="alert-message">${n.message}</div>
                               <button onclick="acknowledgeAlert(${n.id})" class="ack-button">
                                   Acknowledge
                               </button>
                           </div>
                       `).join("");
               });
       }

       function acknowledgeAlert(id) {
           fetch("/api/notifications", {
               method: "PUT",
               headers: {"Content-Type": "application/json"},
               body: JSON.stringify({
                   action: "acknowledge",
                   notification_id: id
               })
           }).then(() => updateAlerts());
       }

       updateAlerts();
       setInterval(updateAlerts, 30000);
       </script>
   </div>
</body>
</html>
