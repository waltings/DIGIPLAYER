<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>DigiPlayer Login</title>
    <link rel="stylesheet" href="/digiplayer/public/assets/css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>DigiPlayer</h2>
                <p>Digital Signage Management</p>
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <div id="loginMessage" class="login-message"></div>
        </div>
    </div>
    <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('/digiplayer/public/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: formData.get('username'),
                    password: formData.get('password')
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                window.location.href = '/digiplayer/public/dashboard.php';
            } else {
                document.getElementById('loginMessage').textContent = data.error || 'Login failed';
            }
        } catch (error) {
            document.getElementById('loginMessage').textContent = 'Connection error occurred';
        }
    });
    </script>
</body>
</html>
