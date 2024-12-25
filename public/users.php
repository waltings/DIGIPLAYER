<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role_name"] !== "admin") { 
   header("Location: /"); exit; 
}
?>
<!DOCTYPE html>
<html>
<head>
   <title>DigiPlayer - User Management</title>
   <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
   <div class="users-container">
       <div class="users-list">
           <h2>Users</h2>
           <div id="usersList"></div>
       </div>

       <div class="user-form">
           <h3>Add New User</h3>
           <form id="userForm">
               <input type="text" name="name" placeholder="Full Name" required>
               <input type="email" name="email" placeholder="Email" required>
               <input type="password" name="password" placeholder="Password" required>
               <select name="role" required>
                   <option value="">Select Role</option>
                   <option value="2">Manager</option>
                   <option value="3">Operator</option>
               </select>
               <button type="submit">Add User</button>
           </form>
       </div>
   </div>

   <script>
   async function loadUsers() {
       const response = await fetch("/api/users");
       const data = await response.json();
       document.getElementById("usersList").innerHTML = data.users.map(user => `
           <div class="user-card">
               <div class="user-info">
                   <h4>${user.name}</h4>
                   <div>${user.email}</div>
                   <div class="role-badge ${user.role_name}">${user.role_name}</div>
               </div>
               <div class="user-actions">
                   <button onclick="resetPassword(${user.id})">Reset Password</button>
                   <button onclick="toggleStatus(${user.id})">Disable</button>
               </div>
           </div>
       `).join("");
   }

   document.getElementById("userForm").onsubmit = async (e) => {
       e.preventDefault();
       const formData = new FormData(e.target);
       await fetch("/api/users", {
           method: "POST",
           headers: {"Content-Type": "application/json"},
           body: JSON.stringify(Object.fromEntries(formData))
       });
       loadUsers();
       e.target.reset();
   };

   loadUsers();
   </script>
</body>
</html>
