import React, { useState, useEffect } from 'react';
import { AlertTriangle, Check, RefreshCw, Settings, Trash2 } from 'lucide-react';

const DeviceManager = () => {
    const [devices, setDevices] = useState([]);
    const [groups, setGroups] = useState([]);
    const [selectedDevice, setSelectedDevice] = useState(null);
    const [isAddingDevice, setIsAddingDevice] = useState(false);
    const [statusFilter, setStatusFilter] = useState('all');
    const [groupFilter, setGroupFilter] = useState('all');

    useEffect(() => {
        loadDevices();
        loadGroups();
        const interval = setInterval(loadDevices, 30000);
        return () => clearInterval(interval);
    }, []);

    const loadDevices = async () => {
        try {
            const response = await fetch('/api/devices/index.php');
            const data = await response.json();
            setDevices(data.devices);
        } catch (error) {
            console.error('Failed to load devices:', error);
        }
    };

    const loadGroups = async () => {
        try {
            const response = await fetch('/api/groups/index.php');
            const data = await response.json();
            setGroups(data.groups);
        } catch (error) {
            console.error('Failed to load groups:', error);
        }
    };

    const handleDeviceAction = async (deviceId, action) => {
        try {
            await fetch('/api/devices/action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: deviceId, action })
            });
            loadDevices();
        } catch (error) {
            console.error(`Failed to ${action} device:`, error);
        }
    };

    const addDevice = async (data) => {
        try {
            await fetch('/api/devices/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            setIsAddingDevice(false);
            loadDevices();
        } catch (error) {
            console.error('Failed to add device:', error);
        }
    };

    const updateDevice = async (deviceId, data) => {
        try {
            await fetch(`/api/devices/index.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: deviceId, ...data })
            });
            setSelectedDevice(null);
            loadDevices();
        } catch (error) {
            console.error('Failed to update device:', error);
        }
    };

    const deleteDevice = async (deviceId) => {
        if (!confirm('Are you sure you want to delete this device?')) return;

        try {
            await fetch('/api/devices/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: deviceId })
            });
            loadDevices();
        } catch (error) {
            console.error('Failed to delete device:', error);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'online': return 'bg-green-500';
            case 'offline': return 'bg-red-500';
            case 'pending': return 'bg-yellow-500';
            default: return 'bg-gray-500';
        }
    };

    const filteredDevices = devices.filter(device => {
        if (statusFilter !== 'all' && device.status !== statusFilter) return false;
        if (groupFilter !== 'all' && device.group_id !== parseInt(groupFilter)) return false;
        return true;
    });

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">Device Management</h2>
                <div className="flex gap-4">
                    <select 
                        className="border rounded p-2"
                        value={statusFilter}
                        onChange={(e) => setStatusFilter(e.target.value)}
                    >
                        <option value="all">All Status</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="pending">Pending</option>
                    </select>
                    <select
                        className="border rounded p-2"
                        value={groupFilter}
                        onChange={(e) => setGroupFilter(e.target.value)}
                    >
                        <option value="all">All Groups</option>
                        {groups.map(group => (
                            <option key={group.id} value={group.id}>
                                {group.name}
                            </option>
                        ))}
                    </select>
                    <button 
                        onClick={() => setIsAddingDevice(true)}
                        className="bg-blue-500 text-white px-4 py-2 rounded"
                    >
                        Add Device
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {filteredDevices.map(device => (
                    <div key={device.id} className="bg-white rounded-lg shadow p-6">
                        <div className="flex justify-between items-start mb-4">
                            <div>
                                <h3 className="text-lg font-semibold">{device.name}</h3>
                                <p className="text-gray-500">{device.ip_address}</p>
                            </div>
                            <span className={`px-2 py-1 rounded text-white text-sm ${getStatusColor(device.status)}`}>
                                {device.status}
                            </span>
                        </div>

                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <div className="bg-gray-50 p-3 rounded">
                                <div className="text-sm text-gray-500">CPU Usage</div>
                                <div className="text-lg">{device.cpu_usage || '0'}%</div>
                            </div>
                            <div className="bg-gray-50 p-3 rounded">
                                <div className="text-sm text-gray-500">Memory</div>
                                <div className="text-lg">{device.memory_usage || '0'}%</div>
                            </div>
                        </div>

                        <div className="flex justify-end gap-2">
                            <button 
                                onClick={() => handleDeviceAction(device.id, 'sync')}
                                className="p-2 text-blue-500 hover:bg-blue-50 rounded"
                            >
                                <RefreshCw size={20} />
                            </button>
                            <button 
                                onClick={() => setSelectedDevice(device)}
                                className="p-2 text-gray-500 hover:bg-gray-50 rounded"
                            >
                                <Settings size={20} />
                            </button>
                            <button 
                                onClick={() => deleteDevice(device.id)}
                                className="p-2 text-red-500 hover:bg-red-50 rounded"
                            >
                                <Trash2 size={20} />
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            {/* Add Device Modal */}
            {isAddingDevice && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-xl font-semibold mb-4">Add New Device</h3>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            const formData = new FormData(e.target);
                            addDevice(Object.fromEntries(formData));
                        }}>
                            <div className="space-y-4">
                                <input
                                    name="name"
                                    placeholder="Device Name"
                                    className="w-full p-2 border rounded"
                                    required
                                />
                                <input
                                    name="ip_address"
                                    placeholder="IP Address"
                                    className="w-full p-2 border rounded"
                                    required
                                />
                                <select
                                    name="group_id"
                                    className="w-full p-2 border rounded"
                                >
                                    <option value="">Select Group</option>
                                    {groups.map(group => (
                                        <option key={group.id} value={group.id}>
                                            {group.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex justify-end gap-2 mt-4">
                                <button 
                                    type="button"
                                    onClick={() => setIsAddingDevice(false)}
                                    className="px-4 py-2 border rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    className="px-4 py-2 bg-blue-500 text-white rounded"
                                >
                                    Add Device
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Edit Device Modal */}
            {selectedDevice && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-xl font-semibold mb-4">Edit Device</h3>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            const formData = new FormData(e.target);
                            updateDevice(selectedDevice.id, Object.fromEntries(formData));
                        }}>
                            <div className="space-y-4">
                                <input
                                    name="name"
                                    defaultValue={selectedDevice.name}
                                    className="w-full p-2 border rounded"
                                    required
                                />
                                <input
                                    name="ip_address"
                                    defaultValue={selectedDevice.ip_address}
                                    className="w-full p-2 border rounded"
                                    required
                                />
                                <select
                                    name="group_id"
                                    defaultValue={selectedDevice.group_id}
                                    className="w-full p-2 border rounded"
                                >
                                    <option value="">Select Group</option>
                                    {groups.map(group => (
                                        <option key={group.id} value={group.id}>
                                            {group.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex justify-end gap-2 mt-4">
                                <button 
                                    type="button"
                                    onClick={() => setSelectedDevice(null)}
                                    className="px-4 py-2 border rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    className="px-4 py-2 bg-blue-500 text-white rounded"
                                >
                                    Update Device
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DeviceManager;
