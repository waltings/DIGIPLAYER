import React, { useState, useEffect, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';

const MediaManager = () => {
    const [mediaFiles, setMediaFiles] = useState([]);
    const [uploading, setUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState({});
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        loadMediaFiles();
    }, []);

    const loadMediaFiles = async () => {
        try {
            const response = await fetch('/api/media/index.php');
            const data = await response.json();
            setMediaFiles(data.media);
        } catch (error) {
            console.error('Failed to load media files:', error);
        }
    };

    const onDrop = useCallback(async (acceptedFiles) => {
        setUploading(true);
        const totalFiles = acceptedFiles.length;
        let completed = 0;

        for (const file of acceptedFiles) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('name', file.name.replace(/\.[^/.]+$/, ''));

            try {
                const response = await fetch('/api/media/index.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('Upload failed');
                
                completed++;
                setUploadProgress(prev => ({
                    ...prev,
                    [file.name]: (completed / totalFiles) * 100
                }));
            } catch (error) {
                console.error(`Failed to upload ${file.name}:`, error);
            }
        }

        setUploading(false);
        loadMediaFiles();
        setUploadProgress({});
    }, []);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: {
            'image/*': ['.jpeg', '.jpg', '.png', '.gif'],
            'video/*': ['.mp4', '.webm']
        }
    });

    const deleteMedia = async (id) => {
        if (!confirm('Are you sure you want to delete this media?')) return;

        try {
            await fetch('/api/media/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            loadMediaFiles();
        } catch (error) {
            console.error('Failed to delete media:', error);
        }
    };

    const filteredMedia = mediaFiles.filter(media => {
        if (filter === 'all') return true;
        return media.type === filter;
    });

    return (
        <div className="media-manager">
            <div className="media-header">
                <h2>Media Library</h2>
                <div className="media-controls">
                    <select 
                        value={filter} 
                        onChange={(e) => setFilter(e.target.value)}
                        className="media-filter"
                    >
                        <option value="all">All Media</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                    </select>
                </div>
            </div>

            <div 
                {...getRootProps()} 
                className={`upload-zone ${isDragActive ? 'active' : ''} ${uploading ? 'uploading' : ''}`}
            >
                <input {...getInputProps()} />
                {uploading ? (
                    <div className="upload-progress">
                        {Object.entries(uploadProgress).map(([filename, progress]) => (
                            <div key={filename} className="progress-item">
                                <div className="filename">{filename}</div>
                                <div className="progress-bar">
                                    <div 
                                        className="progress-fill" 
                                        style={{ width: `${progress}%` }}
                                    />
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="upload-message">
                        {isDragActive ? (
                            <p>Drop files here to upload</p>
                        ) : (
                            <p>Drag & drop files here or click to select</p>
                        )}
                        <p className="upload-info">Supports images (JPG, PNG, GIF) and videos (MP4, WebM)</p>
                    </div>
                )}
            </div>

            <div className="media-grid">
                {filteredMedia.map(media => (
                    <div key={media.id} className="media-card">
                        <div className="media-preview">
                            {media.type === 'video' ? (
                                <video src={media.file_path} controls />
                            ) : (
                                <img src={media.file_path} alt={media.name} />
                            )}
                        </div>
                        <div className="media-info">
                            <div className="media-name">{media.name}</div>
                            <div className="media-meta">
                                <span className={`type-badge type-${media.type}`}>
                                    {media.type}
                                </span>
                                <span className="media-size">
                                    {(media.size / 1024 / 1024).toFixed(1)} MB
                                </span>
                            </div>
                            {media.resolution && (
                                <div className="media-resolution">{media.resolution}</div>
                            )}
                        </div>
                        <div className="media-actions">
                            <button 
                                className="btn-delete" 
                                onClick={() => deleteMedia(media.id)}
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default MediaManager;
