-- Users and Roles indexes
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_status (status);
ALTER TABLE users ADD INDEX idx_role (role_id);

-- Devices indexes
ALTER TABLE devices ADD INDEX idx_status (status);
ALTER TABLE devices ADD INDEX idx_last_heartbeat (last_heartbeat);
ALTER TABLE device_stats ADD INDEX idx_device_created (device_id, created_at);
ALTER TABLE devices ADD INDEX idx_current_playlist (current_playlist_id);

-- Media indexes
ALTER TABLE media ADD INDEX idx_type (type);
ALTER TABLE media ADD INDEX idx_uploaded_by (uploaded_by);
ALTER TABLE media ADD INDEX idx_created_at (created_at);

-- Playlists indexes
ALTER TABLE playlists ADD INDEX idx_status (status);
ALTER TABLE playlists ADD INDEX idx_schedule_type (schedule_type);
ALTER TABLE playlist_media ADD INDEX idx_order (playlist_id, order_number);

-- Schedule indexes
ALTER TABLE schedules ADD INDEX idx_device_time (device_id, start_time, end_time);
ALTER TABLE schedules ADD INDEX idx_exception_date (exception_date);
