document.addEventListener('DOMContentLoaded', function() {
    // Initialize based on current page
    const currentPage = document.body.dataset.page;
    
    switch(currentPage) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'devices':
            // Devices page is handled by devices.js
            break;
        case 'playlists':
            // Playlists page is handled by playlists.js
            break;
        // Add other page initializations as needed
    }
});

// Dashboard functions
function loadDashboardData() {
    // This will only run on the dashboard page
    console.log('Loading dashboard data...');
    // Add dashboard initialization code here
}
