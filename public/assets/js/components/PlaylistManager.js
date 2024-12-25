import React, { useState, useEffect } from 'react';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

const PlaylistManager = () => {
    const [playlists, setPlaylists] = useState([]);
    const [media, setMedia] = useState([]);
    const [selectedPlaylist, setSelectedPlaylist] = useState(null);
    const [playlistItems, setPlaylistItems] = useState([]);

    useEffect(() => {
        loadPlaylists();
        loadMedia();
    }, []);

    const loadPlaylists = async () => {
        try {
            const response = await fetch('/api/playlists/index.php');
            const data = await response.json();
            setPlaylists(data.playlists);
        } catch (error) {
            console.error('Failed to load playlists:', error);
        }
    };

    const loadMedia = async () => {
        try {
            const response = await fetch('/api/media/index.php');
            const data = await response.json();
            setMedia(data.media);
        } catch (error) {
            console.error('Failed to load media:', error);
        }
    };

    const loadPlaylistItems = async (playlistId) => {
        try {
            const response = await fetch(`/api/playlists/media.php?playlist_id=${playlistId}`);
            const data = await response.json();
            setPlaylistItems(data.media);
            setSelectedPlaylist(playlistId);
        } catch (error) {
            console.error('Failed to load playlist items:', error);
        }
    };

    const handleDragEnd = async (result) => {
        if (!result.destination) return;

        const items = Array.from(playlistItems);
        const [reorderedItem] = items.splice(result.source.index, 1);
        items.splice(result.destination.index, 0, reorderedItem);

        setPlaylistItems(items);

        try {
            await fetch(`/api/playlists/media.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    playlist_id: selectedPlaylist,
                    items: items.map(item => item.id)
                })
            });
        } catch (error) {
            console.error('Failed to update playlist order:', error);
        }
    };

    const addToPlaylist = async (mediaId) => {
        if (!selectedPlaylist) return;

        try {
            await fetch('/api/playlists/media.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    playlist_id: selectedPlaylist,
                    media_id: mediaId,
                    order_number: playlistItems.length
                })
            });
            await loadPlaylistItems(selectedPlaylist);
        } catch (error) {
            console.error('Failed to add media to playlist:', error);
        }
    };

    return (
        <div className="playlist-manager">
            <div className="playlists-panel">
                <h3>Playlists</h3>
                <div className="playlist-list">
                    {playlists.map(playlist => (
                        <div 
                            key={playlist.id}
                            className={`playlist-item ${selectedPlaylist === playlist.id ? 'selected' : ''}`}
                            onClick={() => loadPlaylistItems(playlist.id)}
                        >
                            <span>{playlist.name}</span>
                            <span className="item-count">{playlist.items_count} items</span>
                        </div>
                    ))}
                </div>
            </div>

            <div className="content-panel">
                <div className="media-pool">
                    <h3>Available Media</h3>
                    <div className="media-grid">
                        {media.map(item => (
                            <div 
                                key={item.id} 
                                className="media-item"
                                onClick={() => addToPlaylist(item.id)}
                            >
                                <div className="media-preview">
                                    {item.type === 'video' ? (
                                        <video src={item.file_path} />
                                    ) : (
                                        <img src={item.file_path} alt={item.name} />
                                    )}
                                </div>
                                <div className="media-info">
                                    <div className="media-name">{item.name}</div>
                                    <div className="media-type">{item.type}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <DragDropContext onDragEnd={handleDragEnd}>
                    <Droppable droppableId="playlist">
                        {(provided) => (
                            <div 
                                className="playlist-items"
                                {...provided.droppableProps}
                                ref={provided.innerRef}
                            >
                                <h3>Playlist Content</h3>
                                {playlistItems.map((item, index) => (
                                    <Draggable 
                                        key={item.id}
                                        draggableId={item.id.toString()}
                                        index={index}
                                    >
                                        {(provided) => (
                                            <div
                                                ref={provided.innerRef}
                                                {...provided.draggableProps}
                                                {...provided.dragHandleProps}
                                                className="playlist-item"
                                            >
                                                <div className="item-preview">
                                                    {item.type === 'video' ? (
                                                        <video src={item.file_path} />
                                                    ) : (
                                                        <img src={item.file_path} alt={item.name} />
                                                    )}
                                                </div>
                                                <div className="item-info">
                                                    <div className="item-name">{item.name}</div>
                                                    <div className="item-duration">
                                                        {item.duration ? `${Math.round(item.duration)}s` : 'N/A'}
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                    </Draggable>
                                ))}
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>
            </div>
        </div>
    );
};

export default PlaylistManager;
