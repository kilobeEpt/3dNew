import { api } from '../api.js';
import { validateForm, displayFormErrors, clearFormErrors, setupFadeInAnimations } from '../utils.js';

class ContactPage {
    constructor() {
        this.form = document.getElementById('contact-form');
        this.init();
    }

    async init() {
        await this.loadSettings();
        this.setupForm();
        setupFadeInAnimations();
    }

    async loadSettings() {
        try {
            const response = await api.getSettings();
            if (response.success && response.data) {
                this.updateContactInfo(response.data);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    updateContactInfo(settings) {
        const emailEl = document.getElementById('contact-email');
        const phoneEl = document.getElementById('contact-phone');
        const addressEl = document.getElementById('contact-address');

        if (emailEl && settings.contact_email) {
            emailEl.textContent = settings.contact_email;
        }
        if (phoneEl && settings.contact_phone) {
            phoneEl.textContent = settings.contact_phone;
        }
        if (addressEl && settings.contact_address) {
            addressEl.textContent = settings.contact_address;
        }
    }

    setupForm() {
        if (!this.form) return;

        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleSubmit();
        });
    }

    async handleSubmit() {
        clearFormErrors(this.form);
        
        const messagesContainer = document.getElementById('form-messages');
        messagesContainer.innerHTML = '';

        const validation = validateForm(this.form, {
            customer_name: {
                required: true,
                minLength: 2,
                maxLength: 100,
                requiredMessage: 'Name is required',
                minLengthMessage: 'Name must be at least 2 characters'
            },
            customer_email: {
                required: true,
                email: true,
                requiredMessage: 'Email is required',
                emailMessage: 'Please enter a valid email address'
            },
            customer_phone: {
                phone: true,
                phoneMessage: 'Please enter a valid phone number'
            },
            subject: {
                required: true,
                minLength: 5,
                maxLength: 200,
                requiredMessage: 'Subject is required'
            },
            message: {
                required: true,
                minLength: 10,
                maxLength: 2000,
                requiredMessage: 'Message is required',
                minLengthMessage: 'Message must be at least 10 characters'
            }
        });

        if (!validation.isValid) {
            displayFormErrors(this.form, validation.errors);
            messagesContainer.innerHTML = '<div class="alert alert-error" role="alert">Please correct the errors in the form.</div>';
            return;
        }

        const submitButton = this.form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';

        try {
            const formData = new FormData(this.form);
            const data = {
                customer_name: formData.get('customer_name'),
                customer_email: formData.get('customer_email'),
                customer_phone: formData.get('customer_phone') || null,
                subject: formData.get('subject'),
                message: formData.get('message'),
                request_type: 'general'
            };

            const response = await api.submitContact(data);

            if (response.success) {
                messagesContainer.innerHTML = '<div class="alert alert-success" role="status">Thank you! Your message has been sent successfully. We\'ll get back to you soon.</div>';
                this.form.reset();
                setTimeout(() => {
                    messagesContainer.innerHTML = '';
                }, 10000);
            } else {
                throw new Error(response.message || 'Failed to send message');
            }
        } catch (error) {
            console.error('Contact form error:', error);
            let errorMessage = 'Failed to send message. Please try again later.';
            
            if (error.data && error.data.errors) {
                displayFormErrors(this.form, error.data.errors);
                errorMessage = 'Please correct the errors in the form.';
            }
            
            messagesContainer.innerHTML = `<div class="alert alert-error" role="alert">${errorMessage}</div>`;
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ContactPage();
    });
} else {
    new ContactPage();
}

export { ContactPage };
