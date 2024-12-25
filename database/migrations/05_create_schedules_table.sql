CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    playlist_id INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    days_of_week VARCHAR(20) DEFAULT '*',
    priority INT DEFAULT 1,
    is_exception BOOLEAN DEFAULT FALSE,
    exception_date DATE,
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE
);
