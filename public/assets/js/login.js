document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const loginMessage = document.getElementById('loginMessage');

        try {
            const response = await fetch('/digiplayer/public/api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: formData.get('username'),
                    password: formData.get('password')
                })
            });

            const data = await response.json();
            
            if (response.ok) {
                window.location.href = 'dashboard.php';
            } else {
                loginMessage.textContent = data.error || 'Login failed';
                loginMessage.className = 'login-message error';
            }
        } catch (error) {
            console.error('Login error:', error);
            loginMessage.textContent = 'Connection error';
            loginMessage.className = 'login-message error';
        }
    });
});
