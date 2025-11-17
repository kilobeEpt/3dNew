import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { State } from '../state.js';

export class SettingsView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.settings = {};
    }

    async render() {
        if (!State.canManageSettings()) {
            this.container.innerHTML = '<div class="empty-state"><h3>Access Denied</h3><p>You don\'t have permission to manage settings</p></div>';
            return;
        }

        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const response = await API.get('/admin/settings');
            this.settings = this.groupSettings(response.data);
            this.renderContent();
        } catch (error) {
            Toast.error('Error', 'Failed to load settings');
        }
    }

    groupSettings(settings) {
        const grouped = {};
        settings.forEach(setting => {
            const group = setting.group || 'general';
            if (!grouped[group]) {
                grouped[group] = [];
            }
            grouped[group].push(setting);
        });
        return grouped;
    }

    renderContent() {
        const groups = Object.keys(this.settings);
        
        this.container.innerHTML = `
            <div class="toolbar">
                <div class="toolbar-right">
                    <button class="btn btn-primary" id="save-btn">Save Changes</button>
                </div>
            </div>

            <form id="settings-form">
                ${groups.map(group => `
                    <div class="card" style="margin-bottom: var(--spacing-5);">
                        <div class="card-header">
                            <h3 class="card-title">${this.formatGroupName(group)}</h3>
                        </div>
                        <div class="card-body">
                            ${this.settings[group].map(setting => this.renderSettingField(setting)).join('')}
                        </div>
                    </div>
                `).join('')}
            </form>
        `;

        const saveBtn = document.getElementById('save-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveSettings());
        }
    }

    renderSettingField(setting) {
        const inputId = `setting-${setting.key}`;
        
        let inputHtml = '';
        
        switch (setting.type) {
            case 'boolean':
                inputHtml = `<input type="checkbox" id="${inputId}" name="${setting.key}" ${setting.value === 'true' || setting.value === '1' ? 'checked' : ''}>`;
                break;
            case 'number':
            case 'float':
                inputHtml = `<input type="number" step="${setting.type === 'float' ? '0.01' : '1'}" id="${inputId}" name="${setting.key}" value="${setting.value || ''}" class="form-control">`;
                break;
            case 'text':
                inputHtml = `<textarea id="${inputId}" name="${setting.key}" rows="3" class="form-control">${setting.value || ''}</textarea>`;
                break;
            default:
                inputHtml = `<input type="text" id="${inputId}" name="${setting.key}" value="${setting.value || ''}" class="form-control">`;
        }

        return `
            <div class="form-group">
                <label for="${inputId}">${setting.label || setting.key}</label>
                ${inputHtml}
                ${setting.description ? `<div class="form-hint">${setting.description}</div>` : ''}
            </div>
        `;
    }

    formatGroupName(group) {
        return group.split('_').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    async saveSettings() {
        const form = document.getElementById('settings-form');
        const formData = new FormData(form);
        
        const settings = {};
        for (const [key, value] of formData.entries()) {
            settings[key] = value;
        }

        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            settings[checkbox.name] = checkbox.checked ? '1' : '0';
        });

        try {
            await API.post('/admin/settings/bulk', { settings });
            Toast.success('Success', 'Settings saved successfully');
        } catch (error) {
            Toast.error('Error', 'Failed to save settings');
        }
    }
}
