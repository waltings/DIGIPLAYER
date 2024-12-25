<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS playlists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS playlist_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            playlist_id INT NOT NULL,
            media_id INT NOT NULL,
            order_index INT NOT NULL DEFAULT 0,
            duration INT NOT NULL DEFAULT 10,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (playlist_id) REFERENCES playlists(id),
            FOREIGN KEY (media_id) REFERENCES media(id)
        );

        CREATE TABLE IF NOT EXISTS playlist_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            playlist_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            days VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (playlist_id) REFERENCES playlists(id)
        );
    ",
    'down' => "
        DROP TABLE IF EXISTS playlist_schedules;
        DROP TABLE IF EXISTS playlist_items;
        DROP TABLE IF EXISTS playlists;
    "
];
