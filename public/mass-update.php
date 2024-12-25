<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Mass Content Update</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="mass-update-container">
       <div class="selection-panel">
           <div class="groups-select">
               <h3>Select Groups</h3>
               <div id="groupList"></div>
           </div>
           
           <div class="devices-select">
               <h3>Or Select Individual Devices</h3>
               <div id="deviceList"></div>
           </div>
       </div>
       
       <div class="content-panel">
           <h3>Content to Update</h3>
           <form id="contentForm">
               <div class="upload-section">
                   <input type="file" id="contentFiles" multiple>
                   <div class="drag-hint">or drag files here</div>
               </div>
               
               <div class="schedule-section">
                   <label>Schedule Update</label>
                   <select id="scheduleType">
                       <option value="now">Update Now</option>
                       <option value="scheduled">Schedule for Later</option>
                   </select>
                   <input type="datetime-local" id="scheduleTime" style="display:none;">
               </div>
               
               <button type="submit" class="update-button">Start Update</button>
           </form>
       </div>
   </div>

   <script>
   let selectedGroups = new Set();
   let selectedDevices = new Set();

   async function loadGroups() {
       const response = await fetch("/api/groups");
       const data = await response.json();
       document.getElementById("groupList").innerHTML = data.groups.map(g => `
           <div class="group-item" onclick="toggleGroup(${g.id})">
               <input type="checkbox" ${selectedGroups.has(g.id) ? "checked" : ""}>
               <span>${g.name}</span>
           </div>
       `).join("");
   }

   async function loadDevices() {
       const response = await fetch("/api/devices");
       const data = await response.json();
       document.getElementById("deviceList").innerHTML = data.devices.map(d => `
           <div class="device-item" onclick="toggleDevice(${d.id})">
               <input type="checkbox" ${selectedDevices.has(d.id) ? "checked" : ""}>
               <span>${d.name}</span>
           </div>
       `).join("");
   }

   document.getElementById("contentForm").onsubmit = async (e) => {
       e.preventDefault();
       const formData = new FormData();
       formData.append("groups", Array.from(selectedGroups));
       formData.append("devices", Array.from(selectedDevices));
       
       const files = document.getElementById("contentFiles").files;
       for (let file of files) {
           formData.append("files[]", file);
       }
       
       await fetch("/api/mass-update", {
           method: "POST",
           body: formData
       });
       
       alert("Update started!");
   };

   loadGroups();
   loadDevices();
   </script>
</body>
</html>
