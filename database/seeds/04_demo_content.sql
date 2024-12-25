-- Demo media content
INSERT INTO media (name, type, file_path, size, duration, resolution) VALUES
('Welcome Screen', 'image', '/demo/welcome.jpg', 524288, NULL, '1920x1080'),
('Company Logo', 'image', '/demo/logo.png', 262144, NULL, '800x600'),
('Product Demo', 'video', '/demo/product.mp4', 5242880, 30, '1920x1080'),
('News Ticker', 'image', '/demo/news.jpg', 393216, NULL, '1920x1080');

-- Demo playlist
INSERT INTO playlists (name, description, status, created_by) VALUES
('Default Playlist', 'Default content rotation', 'active', 1),
('Welcome Sequence', 'Lobby welcome content', 'active', 1);

-- Demo playlist content
INSERT INTO playlist_media (playlist_id, media_id, order_number, duration) VALUES
(1, 1, 1, 10),
(1, 2, 2, 10),
(1, 3, 3, 30),
(2, 1, 1, 15),
(2, 4, 2, 20);
