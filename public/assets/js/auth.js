document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch("/api/auth/login", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                email: formData.get("email"),
                password: formData.get("password")
            })
        });
        
        if (response.ok) {
            window.location.href = "/dashboard.php";
        } else {
            alert("Login failed");
        }
    } catch (error) {
        console.error("Login error:", error);
    }
});
