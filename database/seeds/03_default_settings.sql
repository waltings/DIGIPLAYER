-- Create settings table if not exists
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (category, key_name)
);

-- Insert default settings
INSERT INTO settings (category, key_name, value) VALUES
-- General Settings
('general', 'system_name', 'DigiPlayer'),
('general', 'timezone', 'Europe/Tallinn'),
('general', 'language', 'en'),
('general', 'default_content_duration', '10'),

-- Storage Settings
('storage', 'media_path', '/uploads/media'),
('storage', 'max_file_size', '104857600'),
('storage', 'allowed_extensions', '["jpg","jpeg","png","gif","mp4","webm"]'),
('storage', 'cleanup_threshold', '90'),

-- Network Settings
('network', 'device_timeout', '300'),
('network', 'sync_interval', '60'),
('network', 'max_retry_attempts', '3'),
('network', 'heartbeat_interval', '30'),

-- Monitoring Settings
('monitoring', 'alert_email', 'alerts@digiplayer.local'),
('monitoring', 'log_retention_days', '30'),
('monitoring', 'stats_retention_days', '90'),
('monitoring', 'enable_error_reporting', 'true'),

-- Playback Settings
('playback', 'default_transition', 'fade'),
('playback', 'transition_duration', '1000'),
('playback', 'enable_audio', 'false'),
('playback', 'default_volume', '50');
