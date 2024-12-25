import React, { useState, useEffect } from 'react';
import { Users, Monitor, Plus, Trash2, Settings, Play } from 'lucide-react';

const GroupManager = () => {
    const [groups, setGroups] = useState([]);
    const [devices, setDevices] = useState([]);
    const [selectedGroup, setSelectedGroup] = useState(null);
    const [isAddingGroup, setIsAddingGroup] = useState(false);
    const [draggedDevice, setDraggedDevice] = useState(null);

    useEffect(() => {
        loadGroups();
        loadDevices();
    }, []);

    const loadGroups = async () => {
        try {
            const response = await fetch('/api/groups/index.php');
            const data = await response.json();
            setGroups(data.groups);
        } catch (error) {
            console.error('Failed to load groups:', error);
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

    const createGroup = async (data) => {
        try {
            await fetch('/api/groups/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            setIsAddingGroup(false);
            loadGroups();
        } catch (error) {
            console.error('Failed to create group:', error);
        }
    };

    const updateGroup = async (groupId, data) => {
        try {
            await fetch(`/api/groups/index.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: groupId, ...data })
            });
            loadGroups();
        } catch (error) {
            console.error('Failed to update group:', error);
        }
    };

    const deleteGroup = async (groupId) => {
        if (!confirm('Are you sure you want to delete this group?')) return;

        try {
            await fetch('/api/groups/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: groupId })
            });
            loadGroups();
        } catch (error) {
            console.error('Failed to delete group:', error);
        }
    };

    const handleDeviceDrop = async (deviceId, groupId) => {
        try {
            await fetch('/api/device-group/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: deviceId, group_id: groupId })
            });
            loadGroups();
        } catch (error) {
            console.error('Failed to assign device to group:', error);
        }
    };

    const handleDeviceRemove = async (deviceId, groupId) => {
        try {
            await fetch('/api/device-group/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: deviceId, group_id: groupId })
            });
            loadGroups();
        } catch (error) {
            console.error('Failed to remove device from group:', error);
        }
    };

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">Group Management</h2>
                <button 
                    onClick={() => setIsAddingGroup(true)}
                    className="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                >
                    <Plus size={20} />
                    Add Group
                </button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Groups Grid */}
                {groups.map(group => (
                    <div 
                        key={group.id}
                        className="bg-white rounded-lg shadow"
                        onDragOver={(e) => {
                            e.preventDefault();
                            e.currentTarget.classList.add('bg-blue-50');
                        }}
                        onDragLeave={(e) => {
                            e.currentTarget.classList.remove('bg-blue-50');
                        }}
                        onDrop={(e) => {
                            e.preventDefault();
                            e.currentTarget.classList.remove('bg-blue-50');
                            if (draggedDevice) {
                                handleDeviceDrop(draggedDevice, group.id);
                            }
                        }}
                    >
                        <div className="p-4 border-b flex justify-between items-center">
                            <div>
                                <h3 className="font-semibold text-lg">{group.name}</h3>
                                <p className="text-sm text-gray-500">{group.description}</p>
                            </div>
                            <div className="flex gap-2">
                                <button 
                                    onClick={() => setSelectedGroup(group)}
                                    className="p-2 text-gray-500 hover:bg-gray-100 rounded"
                                >
                                    <Settings size={18} />
                                </button>
                                <button 
                                    onClick={() => deleteGroup(group.id)}
                                    className="p-2 text-red-500 hover:bg-red-50 rounded"
                                >
                                    <Trash2 size={18} />
                                </button>
                            </div>
                        </div>

                        <div className="p-4">
                            <div className="text-sm text-gray-500 mb-2">
                                {group.devices.length} Devices
                            </div>
                            <div className="space-y-2">
                                {group.devices.map(device => (
                                    <div 
                                        key={device.id}
                                        className="flex justify-between items-center p-2 bg-gray-50 rounded"
                                    >
                                        <div className="flex items-center gap-2">
                                            <Monitor size={16} className="text-gray-400" />
                                            <span>{device.name}</span>
                                        </div>
                                        <button 
                                            onClick={() => handleDeviceRemove(device.id, group.id)}
                                            className="text-gray-400 hover:text-red-500"
                                        >
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Unassigned Devices */}
            <div className="mt-8">
                <h3 className="text-lg font-semibold mb-4">Unassigned Devices</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {devices
                        .filter(device => !groups.some(g => 
                            g.devices.some(d => d.id === device.id)
                        ))
                        .map(device => (
                            <div 
                                key={device.id}
                                className="bg-gray-50 p-3 rounded cursor-move"
                                draggable
                                onDragStart={() => setDraggedDevice(device.id)}
                                onDragEnd={() => setDraggedDevice(null)}
                            >
                                <div className="flex items-center gap-2">
                                    <Monitor size={16} className="text-gray-400" />
                                    <span>{device.name}</span>
                                </div>
                            </div>
                        ))
                    }
                </div>
            </div>

            {/* Add Group Modal */}
            {isAddingGroup && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-xl font-semibold mb-4">Create New Group</h3>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            const formData = new FormData(e.target);
                            createGroup(Object.fromEntries(formData));
                        }}>
                            <div className="space-y-4">
                                <input
                                    name="name"
                                    placeholder="Group Name"
                                    className="w-full p-2 border rounded"
                                    required
                                />
                                <textarea
                                    name="description"
                                    placeholder="Description"
                                    className="w-full p-2 border rounded"
                                    rows="3"
                                />
                            </div>
                            <div className="flex justify-end gap-2 mt-6">
                                <button 
                                    type="button"
                                    onClick={() => setIsAddingGroup(false)}
                                    className="px-4 py-2 border rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    className="px-4 py-2 bg-blue-500 text-white rounded"
                                >
                                    Create Group
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default GroupManager;
