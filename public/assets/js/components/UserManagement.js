import React, { useState, useEffect } from 'react';
import { UserPlus, Edit, Trash2, Key, Shield } from 'lucide-react';

const UserManagement = () => {
    const [users, setUsers] = useState([]);
    const [roles, setRoles] = useState([]);
    const [isAddingUser, setIsAddingUser] = useState(false);
    const [editingUser, setEditingUser] = useState(null);
    const [isChangingPassword, setIsChangingPassword] = useState(false);

    useEffect(() => {
        loadUsers();
        loadRoles();
    }, []);

    const loadUsers = async () => {
        try {
            const response = await fetch('/api/users/index.php');
            const data = await response.json();
            setUsers(data.users);
        } catch (error) {
            console.error('Failed to load users:', error);
        }
    };

    const loadRoles = async () => {
        try {
            const response = await fetch('/api/users/roles.php');
            const data = await response.json();
            setRoles(data.roles);
        } catch (error) {
            console.error('Failed to load roles:', error);
        }
    };

    const handleUserSubmit = async (e, isEdit = false) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userData = Object.fromEntries(formData);

        try {
            await fetch('/api/users/index.php', {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            
            setIsAddingUser(false);
            setEditingUser(null);
            loadUsers();
        } catch (error) {
            console.error('Failed to save user:', error);
        }
    };

    const handlePasswordChange = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            await fetch('/api/users/password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: isChangingPassword.id,
                    new_password: formData.get('new_password')
                })
            });
            
            setIsChangingPassword(false);
        } catch (error) {
            console.error('Failed to change password:', error);
        }
    };

    const deleteUser = async (userId) => {
        if (!confirm('Are you sure you want to delete this user?')) return;

        try {
            await fetch('/api/users/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });
            loadUsers();
        } catch (error) {
            console.error('Failed to delete user:', error);
        }
    };

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">User Management</h2>
                <button 
                    onClick={() => setIsAddingUser(true)}
                    className="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                >
                    <UserPlus size={20} />
                    Add User
                </button>
            </div>

            <div className="bg-white rounded-lg shadow">
                <table className="w-full">
                    <thead>
                        <tr className="border-b">
                            <th className="text-left p-4">Name</th>
                            <th className="text-left p-4">Email</th>
                            <th className="text-left p-4">Role</th>
                            <th className="text-left p-4">Status</th>
                            <th className="text-right p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map(user => (
                            <tr key={user.id} className="border-b hover:bg-gray-50">
                                <td className="p-4">{user.name}</td>
                                <td className="p-4">{user.email}</td>
                                <td className="p-4">
                                    <span className="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                        {user.role_name}
                                    </span>
                                </td>
                                <td className="p-4">
                                    <span className={`px-2 py-1 rounded text-sm ${
                                        user.status === 'active' ? 
                                            'bg-green-100 text-green-800' : 
                                            'bg-red-100 text-red-800'
                                    }`}>
                                        {user.status}
                                    </span>
                                </td>
                                <td className="p-4 text-right">
                                    <button 
                                        onClick={() => setEditingUser(user)}
                                        className="text-gray-600 hover:text-blue-500 p-2"
                                    >
                                        <Edit size={18} />
                                    </button>
                                    <button 
                                        onClick={() => setIsChangingPassword(user)}
                                        className="text-gray-600 hover:text-yellow-500 p-2"
                                    >
                                        <Key size={18} />
                                    </button>
                                    <button 
                                        onClick={() => deleteUser(user.id)}
                                        className="text-gray-600 hover:text-red-500 p-2"
                                    >
                                        <Trash2 size={18} />
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Add/Edit User Modal */}
            {(isAddingUser || editingUser) && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-xl font-semibold mb-4">
                            {editingUser ? 'Edit User' : 'Add New User'}
                        </h3>
                        <form onSubmit={(e) => handleUserSubmit(e, !!editingUser)}>
                            {editingUser && (
                                <input type="hidden" name="id" value={editingUser.id} />
                            )}
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Name
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        defaultValue={editingUser?.name}
                                        className="w-full p-2 border rounded"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        name="email"
                                        defaultValue={editingUser?.email}
                                        className="w-full p-2 border rounded"
                                        required
                                    />
                                </div>
                                {!editingUser && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Password
                                        </label>
                                        <input
                                            type="password"
                                            name="password"
                                            className="w-full p-2 border rounded"
                                            required
                                        />
                                    </div>
                                )}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Role
                                    </label>
                                    <select
                                        name="role_id"
                                        defaultValue={editingUser?.role_id}
                                        className="w-full p-2 border rounded"
                                        required
                                    >
                                        {roles.map(role => (
                                            <option key={role.id} value={role.id}>
                                                {role.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="flex justify-end gap-2 mt-6">
                                <button 
                                    type="button"
                                    onClick={() => {
                                        setIsAddingUser(false);
                                        setEditingUser(null);
                                    }}
                                    className="px-4 py-2 border rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    className="px-4 py-2 bg-blue-500 text-white rounded"
                                >
                                    {editingUser ? 'Update User' : 'Create User'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Change Password Modal */}
            {isChangingPassword && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-xl font-semibold mb-4">
                            Change Password for {isChangingPassword.name}
                        </h3>
                        <form onSubmit={handlePasswordChange}>
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        New Password
                                    </label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        className="w-full p-2 border rounded"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm Password
                                    </label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        className="w-full p-2 border rounded"
                                        required
                                    />
                                </div>
                            </div>
                            <div className="flex justify-end gap-2 mt-6">
                                <button 
                                    type="button"
                                    onClick={() => setIsChangingPassword(false)}
                                    className="px-4 py-2 border rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    className="px-4 py-2 bg-blue-500 text-white rounded"
                                >
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default UserManagement;
