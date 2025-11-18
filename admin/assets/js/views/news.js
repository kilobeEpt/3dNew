import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { Modal } from '../components/modal.js';
import { State } from '../state.js';
import { formatDate, slugify } from '../utils/helpers.js';

export class NewsView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.quillEditor = null;
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const response = await API.get('/admin/news');
            this.renderContent(response.data);
        } catch (error) {
            Toast.error('Error', 'Failed to load news');
        }
    }

    renderContent(data) {
        const canWrite = State.canWrite();
        
        this.container.innerHTML = `
            <div class="toolbar">
                <div class="toolbar-right">
                    ${canWrite ? '<button class="btn btn-primary" id="add-btn">Add Post</button>' : ''}
                </div>
            </div>
            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(data.data || []).map(item => `
                                <tr>
                                    <td><strong>${item.title}</strong></td>
                                    <td><span class="badge ${item.status === 'published' ? 'badge-success' : 'badge-secondary'}">${item.status}</span></td>
                                    <td>${formatDate(item.published_at)}</td>
                                    <td>
                                        <div class="table-actions">
                                            ${canWrite ? `<button class="btn btn-sm btn-secondary" onclick="window.editNews(${item.id})">Edit</button>` : ''}
                                            ${State.canDelete() ? `<button class="btn btn-sm btn-danger" onclick="window.deleteNews(${item.id})">Delete</button>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        const addBtn = document.getElementById('add-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showForm());
        }

        window.editNews = (id) => this.showForm(id);
        window.deleteNews = async (id) => {
            try {
                await Modal.confirm('Delete', 'Delete this post?');
                await API.delete(`/admin/news/${id}`);
                Toast.success('Success', 'Post deleted');
                this.render();
            } catch (error) {
                if (error !== false) Toast.error('Error', 'Failed to delete');
            }
        };
    }

    async showForm(id = null) {
        const content = document.createElement('div');
        content.innerHTML = `
            <form id="news-form">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" id="news-title" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" id="news-slug">
                </div>
                <div class="form-group">
                    <label>Content *</label>
                    <div id="editor-container" style="height: 300px; background: white;"></div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </form>
        `;

        const modal = new Modal({
            title: id ? 'Edit Post' : 'New Post',
            content,
            size: 'large',
            onConfirm: async () => {
                const form = document.getElementById('news-form');
                const formData = new FormData(form);
                const data = {
                    title: formData.get('title'),
                    slug: formData.get('slug') || slugify(formData.get('title')),
                    content: this.quillEditor ? this.quillEditor.root.innerHTML : '',
                    status: formData.get('status')
                };
                
                if (id) {
                    await API.put(`/admin/news/${id}`, data);
                } else {
                    await API.post('/admin/news', data);
                }
                
                Toast.success('Success', 'Post saved');
                this.render();
            }
        });

        await modal.show();

        const titleInput = document.getElementById('news-title');
        const slugInput = document.getElementById('news-slug');
        
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', (e) => {
                if (!id) {
                    slugInput.value = slugify(e.target.value);
                }
            });
        }

        if (typeof Quill !== 'undefined') {
            this.quillEditor = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        ['link', 'image'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            });
        }
    }
}
