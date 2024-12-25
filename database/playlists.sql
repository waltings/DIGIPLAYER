CREATE TABLE IF NOT EXISTS playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS playlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT,
    media_id INT,
    order_index INT,
    duration INT DEFAULT 0,
    FOREIGN KEY (playlist_id) REFERENCES playlists(id)
);
