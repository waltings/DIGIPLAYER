class MediaUploader {
    constructor() {
        this.uploadQueue = new Map();
        this.maxConcurrentUploads = 3;
        this.chunkSize = 1024 * 1024; // 1MB chunks
        this.init();
    }

    init() {
        this.dropZone = document.getElementById('dropZone');
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.dropZone.classList.add('dragover');
        });

        this.dropZone.addEventListener('dragleave', () => {
            this.dropZone.classList.remove('dragover');
        });

        this.dropZone.addEventListener('drop', async (e) => {
            e.preventDefault();
            this.dropZone.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            await this.addToQueue(files);
        });

        document.getElementById('fileInput').addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            await this.addToQueue(files);
        });
    }

    async addToQueue(files) {
        for (const file of files) {
            if (this.validateFile(file)) {
                this.uploadQueue.set(file.name, {
                    file,
                    status: 'pending',
                    progress: 0
                });
                this.updateUploadPreview();
            }
        }
        this.processQueue();
    }

    validateFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'video/mp4'];
        if (!allowedTypes.includes(file.type)) {
            this.showError(`Invalid file type: ${file.type}`);
            return false;
        }
        if (file.size > 100 * 1024 * 1024) { // 100MB limit
            this.showError(`File too large: ${file.name}`);
            return false;
        }
        return true;
    }

    async processQueue() {
        const pending = Array.from(this.uploadQueue.entries())
            .filter(([_, item]) => item.status === 'pending');

        const uploading = Array.from(this.uploadQueue.entries())
            .filter(([_, item]) => item.status === 'uploading');

        while (uploading.length < this.maxConcurrentUploads && pending.length > 0) {
            const [filename, item] = pending.shift();
            item.status = 'uploading';
            this.uploadFile(filename, item.file);
        }
    }

    async uploadFile(filename, file) {
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('name', filename);

            const response = await fetch('/digiplayer/public/api/media/index.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error(`Upload failed: ${response.statusText}`);

            this.uploadQueue.get(filename).status = 'completed';
            this.uploadQueue.get(filename).progress = 100;
            this.updateUploadPreview();
            this.processQueue();

        } catch (error) {
            this.uploadQueue.get(filename).status = 'failed';
            this.showError(`Upload failed for ${filename}: ${error.message}`);
        }
    }

    updateUploadPreview() {
        const preview = document.getElementById('uploadPreview');
        preview.innerHTML = Array.from(this.uploadQueue.entries())
            .map(([filename, item]) => `
                <div class="upload-item ${item.status}">
                    <span class="filename">${filename}</span>
                    <div class="progress-bar">
                        <div class="progress" style="width: ${item.progress}%"></div>
                    </div>
                    <span class="status">${item.status}</span>
                </div>
            `).join('');
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'upload-error';
        errorDiv.textContent = message;
        document.getElementById('uploadPreview').appendChild(errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

// Initialize uploader when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.mediaUploader = new MediaUploader();
});
