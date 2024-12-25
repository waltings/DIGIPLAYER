<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Group Management</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="management-container">
       <div class="groups-panel">
           <h2>Device Groups</h2>
           <div id="groupList"></div>
           <button onclick="showCreateGroupModal()" class="add-button">Create New Group</button>
       </div>
       
       <div class="devices-panel">
           <h2>Available Devices</h2>
           <div id="deviceList" class="drag-container"></div>
       </div>
   </div>

   <div id="createGroupModal" class="modal">
       <div class="modal-content">
           <h3>Create New Group</h3>
           <input type="text" id="groupName" placeholder="Group Name">
           <textarea id="groupDescription" placeholder="Description"></textarea>
           <button onclick="createGroup()">Create</button>
           <button onclick="closeModal()">Cancel</button>
       </div>
   </div>

   <script>
   let draggedItem = null;

   function loadGroups() {
       fetch("/api/groups")
           .then(r => r.json())
           .then(data => {
               document.getElementById("groupList").innerHTML = data.groups.map(g => `
                   <div class="group-card" ondrop="drop(event)" ondragover="allowDrop(event)" data-group-id="${g.id}">
                       <h3>${g.name}</h3>
                       <div class="device-list">
                           ${g.devices.map(d => deviceElement(d)).join("")}
                       </div>
                   </div>
               `).join("");
           });
   }

   function deviceElement(device) {
       return `
           <div class="device-item" draggable="true" ondragstart="drag(event)" data-device-id="${device.id}">
               ${device.name}
           </div>
       `;
   }

   function drag(ev) {
       draggedItem = ev.target;
       ev.dataTransfer.setData("text", ev.target.dataset.deviceId);
   }

   function allowDrop(ev) {
       ev.preventDefault();
   }

   async function drop(ev) {
       ev.preventDefault();
       const deviceId = ev.dataTransfer.getData("text");
       const groupId = ev.target.closest(".group-card").dataset.groupId;
       
       await fetch("/api/group-management", {
           method: "POST",
           headers: {"Content-Type": "application/json"},
           body: JSON.stringify({
               action: "add_devices",
               group_id: groupId,
               device_ids: [deviceId]
           })
       });
       
       loadGroups();
   }

   loadGroups();
   </script>
</body>
</html>
