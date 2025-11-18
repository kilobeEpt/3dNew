import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { State } from '../state.js';
import { compressImage } from '../utils/helpers.js';

export class GalleryView {
    constructor() {
        this.container = document.getElementById('admin-content');
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const response = await API.get('/admin/gallery');
            this.renderContent(response.data);
        } catch (error) {
            Toast.error('Error', 'Failed to load gallery');
        }
    }

    renderContent(data) {
        const canWrite = State.canWrite();
        
        this.container.innerHTML = `
            <div class="toolbar">
                <div class="toolbar-right">
                    ${canWrite ? '<button class="btn btn-primary" id="upload-btn">Upload Image</button>' : ''}
                </div>
            </div>
            <div class="image-preview-container">
                ${(data.data || []).map(item => `
                    <div class="image-preview">
                        <img src="${item.file_path}" alt="${item.title}">
                        ${State.canDelete() ? `<button class="image-preview-remove" onclick="window.deleteGalleryItem(${item.id})">×</button>` : ''}
                    </div>
                `).join('')}
            </div>
        `;

        if (canWrite) {
            const uploadBtn = document.getElementById('upload-btn');
            if (uploadBtn) {
                uploadBtn.addEventListener('click', () => this.showUploadForm());
            }
        }

        window.deleteGalleryItem = async (id) => {
            try {
                await API.delete(`/admin/gallery/${id}`);
                Toast.success('Success', 'Image deleted');
                this.render();
            } catch (error) {
                Toast.error('Error', 'Failed to delete image');
            }
        };
    }

    async showUploadForm() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.multiple = true;
        
        input.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            
            for (const file of files) {
                try {
                    const compressed = await compressImage(file);
                    const formData = new FormData();
                    formData.append('image', compressed);
                    formData.append('title', file.name);
                    
                    await API.upload('/admin/gallery', formData);
                    Toast.success('Success', 'Image uploaded');
                } catch (error) {
                    Toast.error('Error', `Failed to upload ${file.name}`);
                }
            }
            
            this.render();
        });
        
        input.click();
    }
}
