import React, { useState, useEffect } from 'react';
import Calendar from 'react-calendar';
import { Clock, Calendar as CalendarIcon, PlayCircle, Settings, AlertCircle } from 'lucide-react';

const ScheduleManager = () => {
    const [schedules, setSchedules] = useState([]);
    const [playlists, setPlaylists] = useState([]);
    const [devices, setDevices] = useState([]);
    const [selectedDate, setSelectedDate] = useState(new Date());
    const [isAddingSchedule, setIsAddingSchedule] = useState(false);
    const [selectedSchedule, setSelectedSchedule] = useState(null);

    useEffect(() => {
        loadSchedules();
        loadPlaylists();
        loadDevices();
    }, []);

    useEffect(() => {
        loadSchedules(selectedDate);
    }, [selectedDate]);

    const loadSchedules = async (date = null) => {
        try {
            const queryDate = date ? `?date=${date.toISOString().split('T')[0]}` : '';
            const response = await fetch(`/api/schedules/index.php${queryDate}`);
            const data = await response.json();
            setSchedules(data.schedules);
        } catch (error) {
            console.error('Failed to load schedules:', error);
        }
    };

    const loadPlaylists = async () => {
        try {
            const response = await fetch('/api/playlists/index.php');
            const data = await response.json();
            setPlaylists(data.playlists);
        } catch (error) {
            console.error('Failed to load playlists:', error);
        }
    };

    const loadDevices = async () => {
        try {
            const response = await fetch('/api/devices/index.php');
            const data = await response.json();
            setDevices(data.devices);
        } catch (error) {
            console.error('Failed to load devices:', error);
        }
    };

    const addSchedule = async (data) => {
        try {
            await fetch('/api/schedules/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            setIsAddingSchedule(false);
            loadSchedules(selectedDate);
        } catch (error) {
            console.error('Failed to add schedule:', error);
        }
    };

    const updateSchedule = async (id, data) => {
        try {
            await fetch(`/api/schedules/index.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, ...data })
            });
            setSelectedSchedule(null);
            loadSchedules(selectedDate);
        } catch (error) {
            console.error('Failed to update schedule:', error);
        }
    };

    const deleteSchedule = async (id) => {
        if (!confirm('Are you sure you want to delete this schedule?')) return;

        try {
            await fetch('/api/schedules/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            loadSchedules(selectedDate);
        } catch (error) {
            console.error('Failed to delete schedule:', error);
        }
    };

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">Schedule Management</h2>
                <button 
                    onClick={() => setIsAddingSchedule(true)}
                    className="bg-blue-500 text-white px-4 py-2 rounded"
                >
                    Add Schedule
                </button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-1">
                    <div className="bg-white rounded-lg shadow p-4">
                        <Calendar
                            onChange={setSelectedDate}
                            value={selectedDate}
                            className="w-full"
                            tileContent={({ date }) => {
                                const hasSchedule = schedules.some(
                                    s => new Date(s.start_date).toDateString() === date.toDateString()
                                );
                                return hasSchedule ? <div className="schedule-dot"></div> : null;
                            }}
                        />
                    </div>
                </div>

                <div className="lg:col-span-2">
                    <div className="bg-white rounded-lg shadow">
                        <div className="p-4 border-b">
                            <h3 className="text-lg font-semibold">
                                Schedules for {selectedDate.toLocaleDateString()}
                            </h3>
                        </div>
                        <div className="p-4">
                            {schedules.length === 0 ? (
                                <div className="text-center text-gray-500 py-8">
                                    No schedules for this date
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {schedules.map(schedule => (
                                        <div 
                                            key={schedule.id} 
                                            className="border rounded p-4 hover:bg-gray-50"
                                        >
                                            <div className="flex justify-between items-start">
                                                <div>
                                                    <h4 className="font-semibold">
                                                        {playlists.find(p => p.id === schedule.playlist_id)?.name}
                                                    </h4>
                                                    <p className="text-sm text-gray-500">
                                                        {devices.find(d => d.id === schedule.device_id)?.name}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Clock size={16} className="text-gray-400" />
                                                    <span>
                                                        {new Date(schedule.start_time).toLocaleTimeString()} - 
                                                        {new Date(schedule.end_time).toLocaleTimeString()}
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="flex justify-end gap-2 mt-4">
                                                <button 
                                                    onClick={() => setSelectedSchedule(schedule)}
                                                    className="p-2 text-gray-500 hover:bg-gray-100 rounded"
                                                >
                                                    <Settings size={18} />
                                                </button>
                                                <button 
                                                    onClick={() => deleteSchedule(schedule.id)}
                                                    className="p-2 text-red-500 hover:bg-red-50 rounded"
                                                >
                                                    <AlertCircle size={18} />
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Add Schedule Modal */}
            {isAddingSchedule && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-xl font-semibold mb-4">Add New Schedule</h3>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            const formData = new FormData(e.target);
                            addSchedule(Object.fromEntries(formData));
                        }}>
                            <div className="space-y-4">
                                <select
                                    name="playlist_id"
                                    className="w-full p-2 border rounded"
                                    required
                                >
                                    <option value="">Select Playlist</option>
                                    {playlists.map(playlist => (
                                        <option key={playlist.id} value={playlist.id}>
                                            {playlist.name}
                                        </option>
                                    ))}
                                </select>

                                <select
                                    name="device_id"
                                    className="w-full p-2 border rounded"
                                    required
                                >
                                    <option value="">Select Device</option>
                                    {devices.map(device => (
                                        <option key={device.id} value={device.id}>
                                            {device.name}
                                        </option>
                                    ))}
                                </select>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm text-gray-600 mb-1">
                                            Start Time
                                        </label>
                                        <input
                                            type="time"
                                            name="start_time"
                                            className="w-full p-2 border rounded"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm text-gray-600 mb-1">
                                            End Time
                                        </label>
                                        <input
                                            type="time"
                                            name="end_time"
                                            className="w-full p-2 border rounded"
                                            required
                                        />
                                    </div>
                                </div>

                                <select
                                    name="repeat_type"
                                    className="w-full p-2 border rounded"
                                >
                                    <option value="once">Once</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>

                            <div className="flex justify-end gap-2 mt-6">
                                <button 
                                    type="button"
                                    onClick={() => setIsAddingSchedule(false)}
                                    className="px-4 py-2 border rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    className="px-4 py-2 bg-blue-500 text-white rounded"
                                >
                                    Create Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Edit Schedule Modal */}
            {selectedSchedule && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    {/* Similar to Add Schedule Modal but with pre-filled values */}
                </div>
            )}
        </div>
    );
};

export default ScheduleManager;
