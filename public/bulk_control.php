<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Bulk Control</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="bulk-container">
       <div class="selection-panel">
           <h2>Select Devices</h2>
           <label><input type="checkbox" onchange="toggleAll(this)"> Select All</label>
           <div id="deviceList"></div>
       </div>
       
       <div class="control-panel">
           <h2>Bulk Actions</h2>
           <div class="action-buttons">
               <button onclick="bulkAction('restart')">Restart All</button>
               <button onclick="bulkAction('update')">Update All</button>
               <button onclick="bulkAction('reboot')">Reboot All</button>
           </div>
           
           <div class="volume-control">
               <h3>Set Volume All</h3>
               <input type="range" min="0" max="100" onchange="setVolume(this.value)">
           </div>
       </div>
   </div>

   <script>
   let selectedDevices = new Set();

   async function loadDevices() {
       const response = await fetch("/api/devices");
       const data = await response.json();
       document.getElementById("deviceList").innerHTML = data.devices.map(d => `
           <div class="device-item">
               <label>
                   <input type="checkbox" onchange="toggleDevice(${d.id})" ${selectedDevices.has(d.id) ? "checked" : ""}>
                   ${d.name}
               </label>
           </div>
       `).join("");
   }

   function toggleDevice(id) {
       if (selectedDevices.has(id)) {
           selectedDevices.delete(id);
       } else {
           selectedDevices.add(id);
       }
   }

   function toggleAll(checkbox) {
       const checkboxes = document.querySelectorAll(".device-item input[type=checkbox]");
       checkboxes.forEach(box => box.checked = checkbox.checked);
   }

   async function bulkAction(command) {
       if (selectedDevices.size === 0) {
           alert("Please select devices first");
           return;
       }
       
       await fetch("/api/bulk-control", {
           method: "POST",
           headers: {"Content-Type": "application/json"},
           body: JSON.stringify({
               command,
               device_ids: Array.from(selectedDevices),
               params: {}
           })
       });
   }

   loadDevices();
   </script>
</body>
</html>
