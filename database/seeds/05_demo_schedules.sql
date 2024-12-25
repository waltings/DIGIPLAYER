-- Demo schedules
INSERT INTO schedules (device_id, playlist_id, start_time, end_time, days_of_week) VALUES
(1, 1, '09:00:00', '18:00:00', 'MON,TUE,WED,THU,FRI'),
(1, 2, '18:00:00', '09:00:00', 'MON,TUE,WED,THU,FRI'),
(2, 1, '00:00:00', '23:59:59', '*');
