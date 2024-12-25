-- System monitoring table
CREATE TABLE system_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('info', 'warning', 'error', 'critical') NOT NULL,
    component VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_component (type, component),
    INDEX idx_created_at (created_at)
);

-- Performance monitoring
CREATE TABLE performance_metrics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT,
    metric_type VARCHAR(50) NOT NULL,
    value FLOAT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_metric (device_id, metric_type),
    INDEX idx_timestamp (timestamp)
);

-- Content playback logs
CREATE TABLE playback_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    media_id INT NOT NULL,
    playlist_id INT NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    duration INT,
    status ENUM('completed', 'interrupted', 'error') NOT NULL,
    error_message TEXT,
    FOREIGN KEY (device_id) REFERENCES devices(id),
    FOREIGN KEY (media_id) REFERENCES media(id),
    FOREIGN KEY (playlist_id) REFERENCES playlists(id),
    INDEX idx_device_time (device_id, start_time),
    INDEX idx_media_time (media_id, start_time),
    INDEX idx_playlist_time (playlist_id, start_time)
);

-- User activity logging
CREATE TABLE activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    details JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_action (user_id, action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);

-- Alert monitoring
CREATE TABLE alerts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT,
    type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    message TEXT NOT NULL,
    status ENUM('active', 'acknowledged', 'resolved') DEFAULT 'active',
    acknowledged_by INT,
    acknowledged_at TIMESTAMP,
    resolved_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id),
    INDEX idx_device_status (device_id, status),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at)
);

-- Device connectivity logs
CREATE TABLE connectivity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    event_type ENUM('connect', 'disconnect', 'timeout') NOT NULL,
    connection_type VARCHAR(50),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_event (device_id, event_type),
    INDEX idx_created_at (created_at)
);

-- Content synchronization logs
CREATE TABLE sync_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    content_id INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'failed') NOT NULL,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    error_message TEXT,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_status (device_id, status),
    INDEX idx_content (content_type, content_id),
    INDEX idx_started_at (started_at)
);
