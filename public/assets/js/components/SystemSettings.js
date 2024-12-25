import React, { useState, useEffect } from 'react';
import { 
    Settings, Save, Database, Network, Shield, 
    Bell, Clock, HardDrive, Users 
} from 'lucide-react';

const SystemSettings = () => {
    const [activeTab, setActiveTab] = useState('general');
    const [settings, setSettings] = useState({});
    const [loading, setLoading] = useState(true);
    const [saveStatus, setSaveStatus] = useState(null);

    useEffect(() => {
        loadSettings();
    }, [activeTab]);

    const loadSettings = async () => {
        setLoading(true);
        try {
            const response = await fetch(`/api/settings/index.php?category=${activeTab}`);
            const data = await response.json();
            setSettings(data.settings);
        } catch (error) {
            console.error('Failed to load settings:', error);
        }
        setLoading(false);
    };

    const saveSettings = async (updatedSettings) => {
        setSaveStatus('saving');
        try {
            await fetch('/api/settings/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    category: activeTab,
                    settings: updatedSettings
                })
            });
            setSaveStatus('saved');
            setTimeout(() => setSaveStatus(null), 2000);
            loadSettings();
        } catch (error) {
            console.error('Failed to save settings:', error);
            setSaveStatus('error');
        }
    };

    const tabs = [
        { id: 'general', label: 'General', icon: Settings },
        { id: 'storage', label: 'Storage', icon: HardDrive },
        { id: 'network', label: 'Network', icon: Network },
        { id: 'database', label: 'Database', icon: Database },
        { id: 'security', label: 'Security', icon: Shield },
        { id: 'notifications', label: 'Notifications', icon: Bell },
        { id: 'scheduling', label: 'Scheduling', icon: Clock },
        { id: 'permissions', label: 'Permissions', icon: Users }
    ];

    const renderSettingsForm = () => {
        if (loading) {
            return <div className="text-center py-8">Loading settings...</div>;
        }

        switch (activeTab) {
            case 'general':
                return (
                    <form onSubmit={e => {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        saveSettings(Object.fromEntries(formData));
                    }}>
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    System Name
                                </label>
                                <input
                                    type="text"
                                    name="system_name"
                                    defaultValue={settings.system_name}
                                    className="w-full p-2 border rounded"
                                />
                            </div>
                            
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Default Timezone
                                </label>
                                <select
                                    name="timezone"
                                    defaultValue={settings.timezone}
                                    className="w-full p-2 border rounded"
                                >
                                    <option value="UTC">UTC</option>
                                    <option value="Europe/London">London</option>
                                    <option value="Europe/Paris">Paris</option>
                                    <option value="Europe/Tallinn">Tallinn</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Language
                                </label>
                                <select
                                    name="language"
                                    defaultValue={settings.language}
                                    className="w-full p-2 border rounded"
                                >
                                    <option value="en">English</option>
                                    <option value="et">Estonian</option>
                                    <option value="ru">Russian</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Default Content Duration (seconds)
                                </label>
                                <input
                                    type="number"
                                    name="default_duration"
                                    defaultValue={settings.default_duration}
                                    className="w-full p-2 border rounded"
                                />
                            </div>
                        </div>

                        <div className="mt-6">
                            <button 
                                type="submit"
                                className="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                            >
                                <Save size={20} />
                                Save Settings
                            </button>
                        </div>
                    </form>
                );

            case 'storage':
                return (
                    <form onSubmit={e => {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        saveSettings(Object.fromEntries(formData));
                    }}>
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Media Storage Path
                                </label>
                                <input
                                    type="text"
                                    name="media_path"
                                    defaultValue={settings.media_path}
                                    className="w-full p-2 border rounded"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Maximum Storage Size (GB)
                                </label>
                                <input
                                    type="number"
                                    name="max_storage"
                                    defaultValue={settings.max_storage}
                                    className="w-full p-2 border rounded"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Clean Up Policy
                                </label>
                                <select
                                    name="cleanup_policy"
                                    defaultValue={settings.cleanup_policy}
                                    className="w-full p-2 border rounded"
                                >
                                    <option value="manual">Manual</option>
                                    <option value="auto">Automatic</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Retention Period (days)
                                </label>
                                <input
                                    type="number"
                                    name="retention_days"
                                    defaultValue={settings.retention_days}
                                    className="w-full p-2 border rounded"
                                />
                            </div>
                        </div>

                        <div className="mt-6">
                            <button 
                                type="submit"
                                className="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                            >
                                <Save size={20} />
                                Save Settings
                            </button>
                        </div>
                    </form>
                );

            case 'network':
                return (
                    <form onSubmit={e => {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        saveSettings(Object.fromEntries(formData));
                    }}>
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    API Base URL
                                </label>
                                <input
                                    type="text"
                                    name="api_url"
                                    defaultValue={settings.api_url}
                                    className="w-full p-2 border rounded"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Connection Timeout (seconds)
                                </label>
                                <input
                                    type="number"
                                    name="timeout"
                                    defaultValue={settings.timeout}
                                    className="w-full p-2 border rounded"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Max Upload Size (MB)
                                </label>
                                <input
                                    type="number"
                                    name="max_upload_size"
                                    defaultValue={settings.max_upload_size}
                                    className="w-full p-2 border rounded"
                                />
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="enable_ssl"
                                    defaultChecked={settings.enable_ssl}
                                    className="rounded"
                                />
                                <label className="text-sm font-medium text-gray-700">
                                    Enable SSL
                                </label>
                            </div>
                        </div>

                        <div className="mt-6">
                            <button 
                                type="submit"
                                className="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                            >
                                <Save size={20} />
                                Save Settings
                            </button>
                        </div>
                    </form>
                );

            // Add other tab forms here...

            default:
                return null;
        }
    };

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">System Settings</h2>
                {saveStatus && (
                    <div className={`
                        px-4 py-2 rounded
                        ${saveStatus === 'saving' ? 'bg-yellow-100 text-yellow-800' :
                          saveStatus === 'saved' ? 'bg-green-100 text-green-800' :
                          'bg-red-100 text-red-800'}
                    `}>
                        {saveStatus === 'saving' ? 'Saving...' :
                         saveStatus === 'saved' ? 'Settings saved!' :
                         'Error saving settings'}
                    </div>
                )}
            </div>

            <div className="flex gap-6">
                {/* Settings Navigation */}
                <div className="w-64">
                    <div className="bg-white rounded-lg shadow">
                        {tabs.map(tab => (
                            <button
                                key={tab.id}
                                onClick={() => setActiveTab(tab.id)}
                                className={`
                                    w-full px-4 py-3 text-left flex items-center gap-3
                                    ${activeTab === tab.id ? 
                                        'bg-blue-50 text-blue-600 border-l-4 border-blue-500' : 
                                        'text-gray-600 hover:bg-gray-50'}
                                `}
                            >
                                <tab.icon size={20} />
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Settings Content */}
                <div className="flex-1">
                    <div className="bg-white rounded-lg shadow p-6">
                        {renderSettingsForm()}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SystemSettings;
