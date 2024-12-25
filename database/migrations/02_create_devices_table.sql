CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    status ENUM('online', 'offline', 'pending') DEFAULT 'pending',
    current_playlist_id INT,
    current_media_id INT,
    last_heartbeat TIMESTAMP,
    last_sync TIMESTAMP,
    config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE device_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    cpu_usage FLOAT,
    memory_usage FLOAT,
    storage_usage FLOAT,
    temperature FLOAT,
    network_speed FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);
