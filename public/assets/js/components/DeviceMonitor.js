import React, { useState, useEffect } from 'react';
import { 
    LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
    BarChart, Bar, Legend 
} from 'recharts';
import { Activity, Cpu, HardDrive, Wifi, AlertTriangle } from 'lucide-react';

const DeviceMonitor = () => {
    const [deviceStats, setDeviceStats] = useState({});
    const [alerts, setAlerts] = useState([]);
    const [selectedDevice, setSelectedDevice] = useState(null);
    const [timeRange, setTimeRange] = useState('1h');
    const [performanceData, setPerformanceData] = useState([]);

    useEffect(() => {
        loadDeviceStats();
        loadAlerts();
        const interval = setInterval(loadDeviceStats, 30000);
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        if (selectedDevice) {
            loadPerformanceData(selectedDevice, timeRange);
        }
    }, [selectedDevice, timeRange]);

    const loadDeviceStats = async () => {
        try {
            const response = await fetch('/api/monitoring/status.php');
            const data = await response.json();
            setDeviceStats(data.devices);
        } catch (error) {
            console.error('Failed to load device stats:', error);
        }
    };

    const loadAlerts = async () => {
        try {
            const response = await fetch('/api/monitoring/alerts.php');
            const data = await response.json();
            setAlerts(data.alerts);
        } catch (error) {
            console.error('Failed to load alerts:', error);
        }
    };

    const loadPerformanceData = async (deviceId, range) => {
        try {
            const response = await fetch(
                `/api/monitoring/performance.php?device_id=${deviceId}&range=${range}`
            );
            const data = await response.json();
            setPerformanceData(data.performance);
        } catch (error) {
            console.error('Failed to load performance data:', error);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'online': return 'bg-green-500';
            case 'offline': return 'bg-red-500';
            case 'warning': return 'bg-yellow-500';
            default: return 'bg-gray-500';
        }
    };

    return (
        <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {/* Summary Cards */}
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-blue-100 p-3 rounded-lg">
                            <Activity className="text-blue-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Active Devices</div>
                            <div className="text-2xl font-semibold">
                                {Object.values(deviceStats).filter(d => d.status === 'online').length}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-yellow-100 p-3 rounded-lg">
                            <AlertTriangle className="text-yellow-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Active Alerts</div>
                            <div className="text-2xl font-semibold">{alerts.length}</div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-green-100 p-3 rounded-lg">
                            <Cpu className="text-green-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Avg CPU Usage</div>
                            <div className="text-2xl font-semibold">
                                {Math.round(
                                    Object.values(deviceStats)
                                        .reduce((acc, d) => acc + (d.cpu_usage || 0), 0) / 
                                    Object.values(deviceStats).length || 0
                                )}%
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-purple-100 p-3 rounded-lg">
                            <HardDrive className="text-purple-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Avg Memory Usage</div>
                            <div className="text-2xl font-semibold">
                                {Math.round(
                                    Object.values(deviceStats)
                                        .reduce((acc, d) => acc + (d.memory_usage || 0), 0) / 
                                    Object.values(deviceStats).length || 0
                                )}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Device List */}
                <div className="lg:col-span-2">
                    <div className="bg-white rounded-lg shadow">
                        <div className="p-4 border-b">
                            <h3 className="text-lg font-semibold">Devices</h3>
                        </div>
                        <div className="p-4">
                            <div className="space-y-4">
                                {Object.values(deviceStats).map(device => (
                                    <div 
                                        key={device.id}
                                        className={`border rounded p-4 cursor-pointer ${
                                            selectedDevice === device.id ? 'border-blue-500' : ''
                                        }`}
                                        onClick={() => setSelectedDevice(device.id)}
                                    >
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <h4 className="font-semibold">{device.name}</h4>
                                                <p className="text-sm text-gray-500">
                                                    {device.ip_address}
                                                </p>
                                            </div>
                                            <span className={`
                                                px-2 py-1 rounded text-white text-sm 
                                                ${getStatusColor(device.status)}
                                            `}>
                                                {device.status}
                                            </span>
                                        </div>
                                        <div className="grid grid-cols-3 gap-4 mt-4">
                                            <div>
                                                <div className="text-sm text-gray-500">CPU</div>
                                                <div className="text-lg">{device.cpu_usage}%</div>
                                            </div>
                                            <div>
                                                <div className="text-sm text-gray-500">Memory</div>
                                                <div className="text-lg">{device.memory_usage}%</div>
                                            </div>
                                            <div>
                                                <div className="text-sm text-gray-500">Network</div>
                                                <div className="text-lg">
                                                    {device.network_speed} Mbps
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Alerts Panel */}
                <div className="lg:col-span-1">
                    <div className="bg-white rounded-lg shadow">
                        <div className="p-4 border-b">
                            <h3 className="text-lg font-semibold">Active Alerts</h3>
                        </div>
                        <div className="p-4">
                            {alerts.length === 0 ? (
                                <div className="text-center text-gray-500 py-8">
                                    No active alerts
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {alerts.map(alert => (
                                        <div 
                                            key={alert.id}
                                            className={`
                                                border-l-4 rounded p-4
                                                ${alert.severity === 'high' ? 'border-red-500 bg-red-50' :
                                                  alert.severity === 'medium' ? 'border-yellow-500 bg-yellow-50' :
                                                  'border-blue-500 bg-blue-50'}
                                            `}
                                        >
                                            <div className="flex justify-between">
                                                <h4 className="font-semibold">
                                                    {alert.device_name}
                                                </h4>
                                                <span className="text-sm text-gray-500">
                                                    {new Date(alert.created_at).toLocaleTimeString()}
                                                </span>
                                            </div>
                                            <p className="text-sm mt-2">{alert.message}</p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Performance Graphs */}
            {selectedDevice && (
                <div className="mt-6 bg-white rounded-lg shadow p-6">
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-lg font-semibold">Performance History</h3>
                        <select 
                            value={timeRange}
                            onChange={(e) => setTimeRange(e.target.value)}
                            className="border rounded p-2"
                        >
                            <option value="1h">Last Hour</option>
                            <option value="24h">Last 24 Hours</option>
                            <option value="7d">Last 7 Days</option>
                            <option value="30d">Last 30 Days</option>
                        </select>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div className="h-80">
                            <h4 className="text-sm font-semibold mb-4">CPU & Memory Usage</h4>
                            <ResponsiveContainer width="100%" height="100%">
                                <LineChart data={performanceData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="timestamp" />
                                    <YAxis />
                                    <Tooltip />
                                    <Legend />
                                    <Line 
                                        type="monotone" 
                                        dataKey="cpu_usage" 
                                        stroke="#3B82F6" 
                                        name="CPU"
                                    />
                                    <Line 
                                        type="monotone" 
                                        dataKey="memory_usage" 
                                        stroke="#10B981" 
                                        name="Memory"
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>

                        <div className="h-80">
                            <h4 className="text-sm font-semibold mb-4">Network Usage</h4>
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={performanceData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="timestamp" />
                                    <YAxis />
                                    <Tooltip />
                                    <Legend />
                                    <Bar 
                                        dataKey="network_in" 
                                        fill="#3B82F6" 
                                        name="Network In"
                                    />
                                    <Bar 
                                        dataKey="network_out" 
                                        fill="#10B981" 
                                        name="Network Out"
                                    />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DeviceMonitor;
