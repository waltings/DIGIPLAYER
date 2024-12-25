<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Media Queue</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="queue-container">
       <div class="media-list">
           <h2>Available Media</h2>
           <div id="mediaList" class="drag-area"></div>
       </div>
       
       <div class="queue-list">
           <h2>Current Queue</h2>
           <select id="deviceSelect" onchange="loadQueue()"></select>
           <div id="queueList" class="drag-area"></div>
       </div>
   </div>

   <script>
   async function loadMedia() {
       const response = await fetch("/api/media");
       const data = await response.json();
       document.getElementById("mediaList").innerHTML = data.media.map(m => `
           <div class="media-item" draggable="true" ondragstart="drag(event)" data-id="${m.id}">
               <img src="${m.path}" alt="${m.name}">
               <span>${m.name}</span>
           </div>
       `).join("");
   }

   async function loadQueue() {
       const deviceId = document.getElementById("deviceSelect").value;
       const response = await fetch(`/api/queue?device_id=${deviceId}`);
       const data = await response.json();
       document.getElementById("queueList").innerHTML = data.queue.map((item, index) => `
           <div class="queue-item" draggable="true" ondragstart="drag(event)" data-id="${item.id}">
               <span class="order">${index + 1}</span>
               <span>${item.name}</span>
               <input type="number" value="${item.priority}" onchange="updatePriority(${item.id}, this.value)">
           </div>
       `).join("");
   }

   loadMedia();
   loadQueue();
   </script>
</body>
</html>
