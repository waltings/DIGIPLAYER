import React, { useState, useEffect } from 'react';
import { LineChart, XAxis, YAxis, CartesianGrid, Tooltip, Line } from 'recharts';

const Dashboard = () => {
    const [systemStats, setSystemStats] = useState({
        devices: { online: 0, total: 0 },
        storage: { used: 0, total: 0 },
        playback: { today: 0, total: 0 }
    });
    const [performanceData, setPerformanceData] = useState([]);

    useEffect(() => {
        fetchSystemStats();
        fetchPerformanceData();
        const interval = setInterval(fetchSystemStats, 30000);
        return () => clearInterval(interval);
    }, []);

    const fetchSystemStats = async () => {
        try {
            const response = await fetch('/api/monitoring/status.php');
            const data = await response.json();
            setSystemStats(data);
        } catch (error) {
            console.error('Failed to fetch system stats:', error);
        }
    };

    const fetchPerformanceData = async () => {
        try {
            const response = await fetch('/api/analytics/reports.php?period=day');
            const data = await response.json();
            setPerformanceData(data.hourly_stats);
        } catch (error) {
            console.error('Failed to fetch performance data:', error);
        }
    };

    return (
        <div className="dashboard-container">
            <div className="stats-grid">
                <div className="stat-card">
                    <h3>Devices</h3>
                    <div className="stat-value">
                        {systemStats.devices.online} / {systemStats.devices.total}
                    </div>
                    <div className="stat-label">Online</div>
                </div>
                
                <div className="stat-card">
                    <h3>Storage</h3>
                    <div className="stat-value">
                        {Math.round(systemStats.storage.used / 1024 / 1024)} GB
                    </div>
                    <div className="stat-label">Used</div>
                </div>
                
                <div className="stat-card">
                    <h3>Playbacks Today</h3>
                    <div className="stat-value">{systemStats.playback.today}</div>
                </div>
            </div>

            <div className="chart-container">
                <h3>System Performance</h3>
                <ResponsiveContainer width="100%" height={300}>
                    <LineChart data={performanceData}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="hour" />
                        <YAxis />
                        <Tooltip />
                        <Line type="monotone" dataKey="plays" stroke="#8884d8" />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default Dashboard;
