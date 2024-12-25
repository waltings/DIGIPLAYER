CREATE TABLE media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('image', 'video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    size BIGINT,
    duration INT,
    resolution VARCHAR(20),
    checksum VARCHAR(64),
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE media_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    media_id INT NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
);
