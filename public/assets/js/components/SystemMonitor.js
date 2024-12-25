import React, { useState, useEffect } from 'react';

const SystemMonitor = () => {
    const [devices, setDevices] = useState([]);
    const [filter, setFilter] = useState('all');
    
    useEffect(() => {
        loadDevices();
        const interval = setInterval(loadDevices, 30000);
        return () => clearInterval(interval);
    }, []);

    const loadDevices = async () => {
        try {
            const response = await fetch('/api/monitoring/devices.php');
            const data = await response.json();
            setDevices(data.devices);
        } catch (error) {
            console.error('Failed to load devices:', error);
        }
    };

    const getStatusClass = (status) => {
        switch(status) {
            case 'online': return 'status-online';
            case 'offline': return 'status-offline';
            case 'error': return 'status-error';
            default: return 'status-unknown';
        }
    };

    const filteredDevices = devices.filter(device => {
        if (filter === 'all') return true;
        return device.status === filter;
    });

    return (
        <div className="monitor-container">
            <div className="monitor-header">
                <h2>System Monitor</h2>
                <div className="filter-controls">
                    <select 
                        value={filter} 
                        onChange={(e) => setFilter(e.target.value)}
                        className="filter-select"
                    >
                        <option value="all">All Devices</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="error">Error</option>
                    </select>
                </div>
            </div>

            <div className="device-grid">
                {filteredDevices.map(device => (
                    <div key={device.id} className={`device-card ${getStatusClass(device.status)}`}>
                        <div className="device-header">
                            <h3>{device.name}</h3>
                            <span className={`status-badge ${getStatusClass(device.status)}`}>
                                {device.status}
                            </span>
                        </div>
                        <div className="device-stats">
                            <div className="stat-item">
                                <span className="stat-label">CPU</span>
                                <span className="stat-value">{device.cpu_usage}%</span>
                            </div>
                            <div className="stat-item">
                                <span className="stat-label">Memory</span>
                                <span className="stat-value">{device.memory_usage}%</span>
                            </div>
                            <div className="stat-item">
                                <span className="stat-label">Storage</span>
                                <span className="stat-value">{device.storage_usage}%</span>
                            </div>
                        </div>
                        <div className="device-info">
                            <div>Current Playlist: {device.current_playlist || 'None'}</div>
                            <div>Last Update: {new Date(device.last_update).toLocaleString()}</div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default SystemMonitor;
