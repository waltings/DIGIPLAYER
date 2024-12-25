let selectedSchedule = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeSchedule();
    loadSchedules();
    setupEventListeners();
});

function initializeSchedule() {
    // Load playlists and devices for selects
    loadPlaylistOptions();
    loadDeviceOptions();
}

function loadSchedules() {
    fetch('/digiplayer/public/api/schedules')
        .then(response => response.json())
        .then(data => {
            document.getElementById('scheduleData').innerHTML = data.schedules.map(schedule => `
                <tr>
                    <td>${schedule.playlist_name}</td>
                    <td>${schedule.device_name || schedule.group_name}</td>
                    <td>${formatTime(schedule.start_time)}</td>
                    <td>${formatTime(schedule.end_time)}</td>
                    <td>${formatRepeat(schedule.days_of_week)}</td>
                    <td>${schedule.priority}</td>
                    <td>
                        <button class="btn-icon" onclick="editSchedule(${schedule.id})">
                            <i class="icon-edit"></i>
                        </button>
                        <button class="btn-icon" onclick="deleteSchedule(${schedule.id})">
                            <i class="icon-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => console.error('Error loading schedules:', error));
}

function loadPlaylistOptions() {
    fetch('/digiplayer/public/api/playlists')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('playlistSelect');
            select.innerHTML = '<option value="">Select Playlist</option>' +
                data.playlists.map(playlist => 
                    `<option value="${playlist.id}">${playlist.name}</option>`
                ).join('');
        });
}

function loadDeviceOptions() {
    fetch('/digiplayer/public/api/devices')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('deviceSelect');
            select.innerHTML = '<option value="">Select Device</option>' +
                data.devices.map(device => 
                    `<option value="${device.id}">${device.name}</option>`
                ).join('');
        });
}

function setupEventListeners() {
    document.getElementById('scheduleForm').addEventListener('submit', handleScheduleSubmit);
}

function handleScheduleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const scheduleData = {
        playlist_id: formData.get('playlist_id'),
        device_id: formData.get('device_id'),
        start_time: formData.get('start_time'),
        end_time: formData.get('end_time'),
        days_of_week: Array.from(document.querySelectorAll('input[name="days[]"]:checked'))
            .map(cb => cb.value).join(','),
        priority: formData.get('priority')
    };

    const url = '/digiplayer/public/api/schedules';
    const method = selectedSchedule ? 'PUT' : 'POST';
    if (selectedSchedule) {
        scheduleData.id = selectedSchedule;
    }

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(scheduleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeModal('scheduleModal');
            loadSchedules();
            showNotification('Schedule saved successfully', 'success');
        }
    })
    .catch(error => {
        showNotification('Error saving schedule', 'error');
        console.error('Error:', error);
    });
}

function showAddScheduleModal() {
    selectedSchedule = null;
    document.getElementById('scheduleForm').reset();
    document.getElementById('scheduleModal').style.display = 'flex';
}

function editSchedule(id) {
    selectedSchedule = id;
    fetch(`/digiplayer/public/api/schedules/${id}`)
        .then(response => response.json())
        .then(data => {
            const schedule = data.schedule;
            document.getElementById('playlistSelect').value = schedule.playlist_id;
            document.getElementById('deviceSelect').value = schedule.device_id;
            document.getElementById('startTime').value = schedule.start_time;
            document.getElementById('endTime').value = schedule.end_time;
            document.getElementById('priority').value = schedule.priority;
            
            // Set days checkboxes
            const days = schedule.days_of_week.split(',');
            days.forEach(day => {
                document.querySelector(`input[value="${day}"]`).checked = true;
            });
            
            document.getElementById('scheduleModal').style.display = 'flex';
        });
}

function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        fetch(`/digiplayer/public/api/schedules/${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadSchedules();
                showNotification('Schedule deleted successfully', 'success');
            }
        })
        .catch(error => {
            showNotification('Error deleting schedule', 'error');
            console.error('Error:', error);
        });
    }
}

function formatTime(time) {
    return new Date('2000-01-01 ' + time).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function formatRepeat(days) {
    if (days === '*') return 'Daily';
    return days.split(',').join(', ');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function showNotification(message, type) {
    // Implementation depends on your notification system
    console.log(`${type}: ${message}`);
}
