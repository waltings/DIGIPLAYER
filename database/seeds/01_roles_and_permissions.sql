-- Default roles
INSERT INTO roles (name, permissions) VALUES
('admin', '{
    "users": ["create", "read", "update", "delete"],
    "devices": ["create", "read", "update", "delete"],
    "media": ["create", "read", "update", "delete"],
    "playlists": ["create", "read", "update", "delete"],
    "schedules": ["create", "read", "update", "delete"],
    "groups": ["create", "read", "update", "delete"],
    "settings": ["read", "update"],
    "monitoring": ["read"]
}'),
('manager', '{
    "devices": ["read", "update"],
    "media": ["create", "read", "update"],
    "playlists": ["create", "read", "update"],
    "schedules": ["create", "read", "update"],
    "groups": ["read"],
    "monitoring": ["read"]
}'),
('operator', '{
    "devices": ["read"],
    "media": ["read"],
    "playlists": ["read"],
    "schedules": ["read"],
    "monitoring": ["read"]
}');

-- Default admin user (password: Digiplayer1-401)
INSERT INTO users (name, email, password, role_id, status) VALUES
('Admin', 'admin@digiplayer.local', '$2y$10$KN8nKj1i98AV8HmTD.4jhu5SWU0.FrCQ3G6TrQZPQyf40eL5Ck5Fi', 1, 'active');
