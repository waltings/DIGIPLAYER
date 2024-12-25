<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Sync Status</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <nav>
       <a href="/dashboard.php">Dashboard</a>
       <a href="/devices.php">Devices</a>
       <a href="/sync.php">Sync Status</a>
   </nav>
   
   <div class="sync-container">
       <h1>Synchronization Status</h1>
       <div id="syncQueue"></div>
       <button onclick="syncAll()" class="sync-button">Sync All Devices</button>
   </div>

   <script>
   function loadSyncStatus() {
       fetch("/api/sync")
           .then(r => r.json())
           .then(data => {
               document.getElementById("syncQueue").innerHTML = `
                   <div class="sync-grid">
                       ${data.queue.map(q => `
                           <div class="sync-item ${q.status}">
                               <div class="sync-header">
                                   <span>Device: ${q.device_id}</span>
                                   <span>Status: ${q.status}</span>
                               </div>
                               <div class="sync-details">
                                   <div>Action: ${q.action}</div>
                                   <div>Created: ${q.created_at}</div>
                               </div>
                           </div>
                       `).join("")}
                   </div>
               `;
           });
   }

   function syncAll() {
       fetch("/api/sync", {
           method: "POST",
           headers: {"Content-Type": "application/json"},
           body: JSON.stringify({
               action: "update_all",
               data: {}
           })
       }).then(() => loadSyncStatus());
   }

   setInterval(loadSyncStatus, 10000);
   loadSyncStatus();
   </script>
</body>
</html>
