import React, { useState, useEffect } from 'react';
import { 
    LineChart, Line, BarChart, Bar, PieChart, Pie, Cell,
    XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer 
} from 'recharts';
import { 
    TrendingUp, Users, Calendar, Clock, 
    Monitor, Play, AlertCircle, Download 
} from 'lucide-react';

const AnalyticsDashboard = () => {
    const [timeRange, setTimeRange] = useState('7d');
    const [playbackData, setPlaybackData] = useState([]);
    const [deviceStats, setDeviceStats] = useState({});
    const [contentStats, setContentStats] = useState({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadAnalytics();
    }, [timeRange]);

    const loadAnalytics = async () => {
        setLoading(true);
        try {
            const response = await fetch(`/api/analytics/reports.php?period=${timeRange}`);
            const data = await response.json();
            
            setPlaybackData(data.playback_data);
            setDeviceStats(data.device_stats);
            setContentStats(data.content_stats);
        } catch (error) {
            console.error('Failed to load analytics:', error);
        }
        setLoading(false);
    };

    const generateReport = async () => {
        try {
            const response = await fetch('/api/analytics/generate-report.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ period: timeRange })
            });
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `analytics_report_${timeRange}.pdf`;
            a.click();
        } catch (error) {
            console.error('Failed to generate report:', error);
        }
    };

    const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">Analytics Dashboard</h2>
                <div className="flex gap-4">
                    <select 
                        value={timeRange}
                        onChange={(e) => setTimeRange(e.target.value)}
                        className="border rounded p-2"
                    >
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 90 Days</option>
                    </select>
                    <button 
                        onClick={generateReport}
                        className="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                    >
                        <Download size={20} />
                        Export Report
                    </button>
                </div>
            </div>

            {/* Summary Stats */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-blue-100 p-3 rounded-lg">
                            <Play className="text-blue-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Total Playbacks</div>
                            <div className="text-2xl font-semibold">
                                {contentStats.total_plays?.toLocaleString()}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-green-100 p-3 rounded-lg">
                            <Clock className="text-green-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Total Hours Played</div>
                            <div className="text-2xl font-semibold">
                                {Math.round(contentStats.total_duration / 3600)}h
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-yellow-100 p-3 rounded-lg">
                            <Monitor className="text-yellow-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Active Devices</div>
                            <div className="text-2xl font-semibold">
                                {deviceStats.active_devices}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-4">
                        <div className="bg-red-100 p-3 rounded-lg">
                            <AlertCircle className="text-red-500" size={24} />
                        </div>
                        <div>
                            <div className="text-sm text-gray-500">Error Rate</div>
                            <div className="text-2xl font-semibold">
                                {deviceStats.error_rate?.toFixed(2)}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-semibold mb-4">Playback Trends</h3>
                    <div className="h-80">
                        <ResponsiveContainer width="100%" height="100%">
                            <LineChart data={playbackData}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="date" />
                                <YAxis />
                                <Tooltip />
                                <Legend />
                                <Line 
                                    type="monotone" 
                                    dataKey="plays" 
                                    stroke="#3B82F6" 
                                    name="Plays"
                                />
                                <Line 
                                    type="monotone" 
                                    dataKey="unique_devices" 
                                    stroke="#10B981" 
                                    name="Unique Devices"
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-semibold mb-4">Content Type Distribution</h3>
                    <div className="h-80">
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <Pie
                                    data={contentStats.type_distribution}
                                    dataKey="value"
                                    nameKey="name"
                                    cx="50%"
                                    cy="50%"
                                    outerRadius={100}
                                    fill="#8884d8"
                                >
                                    {contentStats.type_distribution?.map((entry, index) => (
                                        <Cell 
                                            key={`cell-${index}`} 
                                            fill={COLORS[index % COLORS.length]} 
                                        />
                                    ))}
                                </Pie>
                                <Tooltip />
                                <Legend />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </div>

            {/* Top Content Table */}
            <div className="bg-white rounded-lg shadow">
                <div className="p-4 border-b">
                    <h3 className="text-lg font-semibold">Top Performing Content</h3>
                </div>
                <div className="p-4">
                    <table className="w-full">
                        <thead>
                            <tr>
                                <th className="text-left p-2">Content Name</th>
                                <th className="text-left p-2">Type</th>
                                <th className="text-right p-2">Plays</th>
                                <th className="text-right p-2">Duration</th>
                                <th className="text-right p-2">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            {contentStats.top_content?.map((content, index) => (
                                <tr 
                                    key={content.id}
                                    className={index % 2 === 0 ? 'bg-gray-50' : ''}
                                >
                                    <td className="p-2">{content.name}</td>
                                    <td className="p-2">{content.type}</td>
                                    <td className="text-right p-2">
                                        {content.plays.toLocaleString()}
                                    </td>
                                    <td className="text-right p-2">
                                        {Math.round(content.duration / 60)}m
                                    </td>
                                    <td className="text-right p-2">
                                        {content.completion_rate}%
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default AnalyticsDashboard;
