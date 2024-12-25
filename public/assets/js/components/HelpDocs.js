import React, { useState } from 'react';
import { Search, Book, Video, HelpCircle, FileText, ExternalLink } from 'lucide-react';

const HelpDocs = () => {
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('getting-started');

    const categories = [
        { id: 'getting-started', label: 'Getting Started', icon: Book },
        { id: 'tutorials', label: 'Video Tutorials', icon: Video },
        { id: 'faq', label: 'FAQ', icon: HelpCircle },
        { id: 'api-docs', label: 'API Documentation', icon: FileText }
    ];

    const content = {
        'getting-started': [
            {
                title: 'System Overview',
                content: `
                    DigiPlayer is a comprehensive digital signage system designed for managing 
                    content across multiple displays. This guide will help you understand the 
                    basic concepts and get started with the system.
                `
            },
            {
                title: 'First Steps',
                content: `
                    1. Add your first device
                    2. Upload media content
                    3. Create a playlist
                    4. Schedule content
                `
            }
        ],
        'tutorials': [
            {
                title: 'Device Setup',
                videoUrl: 'https://example.com/tutorials/device-setup',
                thumbnail: '/assets/images/tutorials/device-setup.jpg'
            },
            {
                title: 'Content Management',
                videoUrl: 'https://example.com/tutorials/content-management',
                thumbnail: '/assets/images/tutorials/content-management.jpg'
            }
        ],
        'faq': [
            {
                question: 'How do I add a new device?',
                answer: `
                    To add a new device:
                    1. Go to Devices section
                    2. Click "Add Device"
                    3. Enter device details
                    4. Save the configuration
                `
            },
            {
                question: 'What media formats are supported?',
                answer: `
                    DigiPlayer supports the following formats:
                    - Images: JPG, PNG, GIF
                    - Videos: MP4, WebM
                    - Maximum file size: 100MB
                `
            }
        ],
        'api-docs': [
            {
                title: 'Authentication',
                content: `
                    All API requests require authentication using JWT tokens.
                    Request headers must include:
                    Authorization: Bearer <your-token>
                `
            },
            {
                title: 'Endpoints',
                content: `
                    GET /api/devices - List all devices
                    POST /api/devices - Create new device
                    GET /api/media - List all media
                    POST /api/media - Upload new media
                `
            }
        ]
    };

    const filteredContent = content[selectedCategory].filter(item => 
        (item.title?.toLowerCase().includes(searchQuery.toLowerCase()) ||
         item.question?.toLowerCase().includes(searchQuery.toLowerCase()) ||
         item.content?.toLowerCase().includes(searchQuery.toLowerCase()) ||
         item.answer?.toLowerCase().includes(searchQuery.toLowerCase()))
    );

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-semibold">Help & Documentation</h2>
                <div className="relative">
                    <input
                        type="text"
                        placeholder="Search documentation..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="w-64 p-2 pl-10 border rounded"
                    />
                    <Search className="absolute left-3 top-2.5 text-gray-400" size={20} />
                </div>
            </div>

            <div className="flex gap-6">
                {/* Categories Navigation */}
                <div className="w-64">
                    <div className="bg-white rounded-lg shadow">
                        {categories.map(category => (
                            <button
                                key={category.id}
                                onClick={() => setSelectedCategory(category.id)}
                                className={`
                                    w-full px-4 py-3 text-left flex items-center gap-3
                                    ${selectedCategory === category.id ? 
                                        'bg-blue-50 text-blue-600 border-l-4 border-blue-500' : 
                                        'text-gray-600 hover:bg-gray-50'}
                                `}
                            >
                                <category.icon size={20} />
                                {category.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Content Area */}
                <div className="flex-1">
                    <div className="bg-white rounded-lg shadow p-6">
                        {selectedCategory === 'tutorials' ? (
                            <div className="grid grid-cols-2 gap-6">
                                {filteredContent.map((tutorial, index) => (
                                    <div key={index} className="border rounded-lg overflow-hidden">
                                        <img 
                                            src={tutorial.thumbnail} 
                                            alt={tutorial.title} 
                                            className="w-full h-48 object-cover"
                                        />
                                        <div className="p-4">
                                            <h3 className="font-semibold mb-2">{tutorial.title}</h3>
                                            <a 
                                                href={tutorial.videoUrl}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-blue-500 flex items-center gap-2"
                                            >
                                                Watch Tutorial
                                                <ExternalLink size={16} />
                                            </a>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : selectedCategory === 'faq' ? (
                            <div className="space-y-6">
                                {filteredContent.map((faq, index) => (
                                    <div key={index} className="border-b pb-6 last:border-b-0">
                                        <h3 className="font-semibold mb-2">{faq.question}</h3>
                                        <p className="text-gray-600 whitespace-pre-line">{faq.answer}</p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="space-y-8">
                                {filteredContent.map((item, index) => (
                                    <div key={index}>
                                        <h3 className="font-semibold text-lg mb-2">{item.title}</h3>
                                        <p className="text-gray-600 whitespace-pre-line">{item.content}</p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default HelpDocs;
