<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Device Locations</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="locations-container">
       <div class="floor-selector">
           <select id="floorSelect" onchange="changeFloor()">
               <option value="1">Floor 1</option>
               <option value="2">Floor 2</option>
           </select>
       </div>
       
       <div class="map-container">
           <div id="floorMap" class="floor-map"></div>
       </div>
       
       <div class="device-list">
           <h3>Devices</h3>
           <div id="deviceList"></div>
       </div>
   </div>

   <script>
   let devices = [];
   let selectedDevice = null;

   async function loadDevices() {
       const response = await fetch("/api/locations");
       const data = await response.json();
       devices = data.locations;
       updateMap();
       updateDeviceList();
   }

   function updateMap() {
       const floor = document.getElementById("floorSelect").value;
       const floorDevices = devices.filter(d => d.floor === floor);
       
       const map = document.getElementById("floorMap");
       map.innerHTML = floorDevices.map(d => `
           <div class="device-marker ${d.status}"
                style="left: ${d.coordinates.x}%; top: ${d.coordinates.y}%"
                onclick="selectDevice(${d.device_id})">
               ${d.device_name}
           </div>
       `).join("");
   }

   loadDevices();
   </script>
</body>
</html>
