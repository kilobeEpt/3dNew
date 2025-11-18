import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { State } from '../state.js';

export class PricingRulesView {
    constructor() {
        this.container = document.getElementById('admin-content');
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const response = await API.get('/admin/pricing-rules');
            this.renderContent(response.data);
        } catch (error) {
            Toast.error('Error', 'Failed to load pricing rules');
        }
    }

    renderContent(data) {
        this.container.innerHTML = `
            <div class="toolbar">
                <div class="toolbar-right">
                    ${State.canWrite() ? '<button class="btn btn-primary" id="add-btn">Add Rule</button>' : ''}
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <p>Pricing rules management - ${(data.data || []).length} rules configured</p>
                </div>
            </div>
        `;
    }
}
