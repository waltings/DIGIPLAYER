<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - Scheduled Commands</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="schedule-container">
       <div class="schedule-list">
           <h2>Scheduled Commands</h2>
           <div id="scheduleList"></div>
           <button onclick="showAddScheduleModal()" class="add-button">Add Schedule</button>
       </div>
       
       <div id="scheduleModal" class="modal">
           <div class="modal-content">
               <h3>Add Scheduled Command</h3>
               <select id="deviceSelect"></select>
               <select id="commandSelect">
                   <option value="restart">Restart</option>
                   <option value="update">Update</option>
                   <option value="sync">Sync Content</option>
               </select>
               <select id="scheduleType">
                   <option value="once">Once</option>
                   <option value="daily">Daily</option>
                   <option value="weekly">Weekly</option>
                   <option value="monthly">Monthly</option>
               </select>
               <input type="time" id="scheduleTime">
               <input type="date" id="scheduleDate">
               <button onclick="createSchedule()">Create</button>
           </div>
       </div>
   </div>

   <script>
   async function loadSchedules() {
       const response = await fetch("/api/schedules");
       const data = await response.json();
       document.getElementById("scheduleList").innerHTML = data.commands.map(c => `
           <div class="schedule-item">
               <div class="schedule-header">
                   <span>${c.device_name}</span>
                   <span>${c.command}</span>
               </div>
               <div class="schedule-details">
                   <div>Type: ${c.schedule_type}</div>
                   <div>Time: ${c.schedule_time}</div>
                   <div>Next Run: ${c.next_run}</div>
               </div>
           </div>
       `).join("");
   }

   async function createSchedule() {
       const data = {
           device_id: document.getElementById("deviceSelect").value,
           command: document.getElementById("commandSelect").value,
           schedule_type: document.getElementById("scheduleType").value,
           schedule_time: document.getElementById("scheduleTime").value,
           schedule_day: document.getElementById("scheduleDate").value
       };

       await fetch("/api/schedules", {
           method: "POST",
           headers: {"Content-Type": "application/json"},
           body: JSON.stringify(data)
       });
       
       loadSchedules();
       closeModal();
   }

   loadSchedules();
   </script>
</body>
</html>
