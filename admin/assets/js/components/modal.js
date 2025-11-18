export class Modal {
    constructor(options = {}) {
        this.title = options.title || 'Modal';
        this.content = options.content || '';
        this.size = options.size || 'medium';
        this.onConfirm = options.onConfirm || null;
        this.onCancel = options.onCancel || null;
        this.confirmText = options.confirmText || 'Confirm';
        this.cancelText = options.cancelText || 'Cancel';
        this.showFooter = options.showFooter !== false;
        
        this.container = document.getElementById('modal-container');
        this.modal = null;
    }

    show() {
        this.modal = this.create();
        this.container.innerHTML = '';
        this.container.appendChild(this.modal);
        this.container.classList.add('active');
        
        this.attachEventListeners();
        
        return new Promise((resolve, reject) => {
            this.resolvePromise = resolve;
            this.rejectPromise = reject;
        });
    }

    create() {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';

        const modal = document.createElement('div');
        modal.className = `modal modal-${this.size}`;

        const header = document.createElement('div');
        header.className = 'modal-header';
        header.innerHTML = `
            <h3 class="modal-title">${this.escapeHtml(this.title)}</h3>
            <button class="modal-close" data-action="close">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;

        const body = document.createElement('div');
        body.className = 'modal-body';
        if (typeof this.content === 'string') {
            body.innerHTML = this.content;
        } else {
            body.appendChild(this.content);
        }

        modal.appendChild(header);
        modal.appendChild(body);

        if (this.showFooter) {
            const footer = document.createElement('div');
            footer.className = 'modal-footer';
            footer.innerHTML = `
                <button class="btn btn-secondary" data-action="cancel">${this.escapeHtml(this.cancelText)}</button>
                <button class="btn btn-primary" data-action="confirm">${this.escapeHtml(this.confirmText)}</button>
            `;
            modal.appendChild(footer);
        }

        const wrapper = document.createElement('div');
        wrapper.appendChild(backdrop);
        wrapper.appendChild(modal);

        return wrapper;
    }

    attachEventListeners() {
        const backdrop = this.modal.querySelector('.modal-backdrop');
        const closeBtn = this.modal.querySelector('[data-action="close"]');
        const cancelBtn = this.modal.querySelector('[data-action="cancel"]');
        const confirmBtn = this.modal.querySelector('[data-action="confirm"]');

        backdrop?.addEventListener('click', () => this.handleCancel());
        closeBtn?.addEventListener('click', () => this.handleCancel());
        cancelBtn?.addEventListener('click', () => this.handleCancel());
        confirmBtn?.addEventListener('click', () => this.handleConfirm());
    }

    async handleConfirm() {
        if (this.onConfirm) {
            try {
                const result = await this.onConfirm();
                this.resolvePromise(result);
                this.close();
            } catch (error) {
                console.error('Modal confirm error:', error);
            }
        } else {
            this.resolvePromise(true);
            this.close();
        }
    }

    handleCancel() {
        if (this.onCancel) {
            this.onCancel();
        }
        this.rejectPromise(false);
        this.close();
    }

    close() {
        this.container.classList.remove('active');
        setTimeout(() => {
            this.container.innerHTML = '';
        }, 200);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    static confirm(title, message, confirmText = 'Confirm', cancelText = 'Cancel') {
        const modal = new Modal({
            title,
            content: `<p>${message}</p>`,
            confirmText,
            cancelText
        });
        return modal.show();
    }

    static alert(title, message, buttonText = 'OK') {
        const modal = new Modal({
            title,
            content: `<p>${message}</p>`,
            confirmText: buttonText,
            showFooter: true,
            onConfirm: null
        });
        
        const modalEl = modal.show();
        const cancelBtn = modal.modal.querySelector('[data-action="cancel"]');
        if (cancelBtn) {
            cancelBtn.style.display = 'none';
        }
        
        return modalEl;
    }
}
