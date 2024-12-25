<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>DigiPlayer - Remote Control</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="remote-container">
        <div id="deviceList" class="device-grid"></div>
        
        <div class="command-panel" id="commandPanel">
            <h3>Device Control</h3>
            <div class="command-buttons">
                <button onclick="sendCommand('restart')">Restart</button>
                <button onclick="sendCommand('refresh')">Refresh Content</button>
                <button onclick="sendCommand('clear-cache')">Clear Cache</button>
                <button onclick="sendCommand('screenshot')">Take Screenshot</button>
            </div>
            
           <div class="volume-control">
               <label>Volume</label>
               <input type="range" min="0" max="100" onchange="setVolume(this.value)">
           </div>
           
           <div class="playlist-control">
               <button onclick="sendCommand('prev')">Previous</button>
               <button onclick="sendCommand('play-pause')">Play/Pause</button>
               <button onclick="sendCommand('next')">Next</button>
           </div>
       </div>
   </div>

   <script>
   async function loadDevices() {
       const response = await fetch("/api/devices");
       const data = await response.json();
       document.getElementById("deviceList").innerHTML = data.devices.map(d => `
           <div class="device-card ${d.status}" onclick="selectDevice(${d.id})">
               <h3>${d.name}</h3>
               <div class="status-info">Status: ${d.status}</div>
           </div>
       `).join("");
   }

   let selectedDevice = null;

   function selectDevice(id) {
       selectedDevice = id;
       document.querySelectorAll(".device-card").forEach(card => {
           card.classList.remove("selected");
       });
       document.querySelector(`[onclick="selectDevice(${id})"]`).classList.add("selected");
   }

   async function sendCommand(command, params = {}) {
       if (!selectedDevice) {
           alert("Please select a device first");
           return;
       }
       
       await fetch("/api/remote", {
           method: "POST",
           headers: {"Content-Type": "application/json"},
           body: JSON.stringify({
               command,
               device_id: selectedDevice,
               params
           })
       });
   }

   function setVolume(value) {
       sendCommand("set-volume", { level: value });
   }

   loadDevices();
   setInterval(loadDevices, 10000);
   </script>
</body>
</html>
