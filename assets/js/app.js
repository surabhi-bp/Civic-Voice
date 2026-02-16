/**
 * Theme Toggle - Dark/Light Mode
 */

class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }
    
    init() {
        this.applyTheme(this.theme);
        this.setupToggle();
    }
    
    applyTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        this.theme = theme;
        localStorage.setItem('theme', theme);
    }
    
    toggle() {
        const newTheme = this.theme === 'dark' ? 'light' : 'dark';
        this.applyTheme(newTheme);
    }
    
    setupToggle() {
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggle());
        }
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ThemeManager();
});

/**
 * Mobile Menu Toggle
 */

class MobileMenu {
    constructor() {
        this.toggle = document.querySelector('.navbar-toggle');
        this.menu = document.querySelector('.navbar-menu');
        this.init();
    }
    
    init() {
        if (this.toggle && this.menu) {
            this.toggle.addEventListener('click', () => this.toggleMenu());
            
            // Close menu when a link is clicked
            this.menu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => this.closeMenu());
            });
        }
    }
    
    toggleMenu() {
        this.menu.classList.toggle('active');
    }
    
    closeMenu() {
        this.menu.classList.remove('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new MobileMenu();
});

/**
 * Sidebar Toggle
 */

class Sidebar {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.toggleBtn = document.querySelector('.sidebar-toggle');
        this.closeBtn = document.querySelector('.sidebar-close');
        this.init();
    }
    
    init() {
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }
        
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.close());
        }
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (this.sidebar && !this.sidebar.contains(e.target) && !this.toggleBtn?.contains(e.target)) {
                this.close();
            }
        });
    }
    
    toggle() {
        this.sidebar?.classList.toggle('active');
    }
    
    close() {
        this.sidebar?.classList.remove('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Sidebar();
});

/**
 * Modal Handler
 */

class ModalHandler {
    static open(modalId) {
        const modal = document.getElementById(modalId);
        const overlay = modal?.closest('.modal-overlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.classList.add('no-scroll');
        }
    }
    
    static close(modalId) {
        const modal = document.getElementById(modalId);
        const overlay = modal?.closest('.modal-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            document.body.classList.remove('no-scroll');
        }
    }
    
    static closeAll() {
        document.querySelectorAll('.modal-overlay.active').forEach(overlay => {
            overlay.classList.remove('active');
        });
        document.body.classList.remove('no-scroll');
    }
}

// Close modal on overlay click or close button click
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                ModalHandler.closeAll();
            }
        });
    });
    
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            ModalHandler.closeAll();
        });
    });
});

/**
 * Form Validation
 */

class FormValidator {
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    static validatePassword(password) {
        return password.length >= 8;
    }
    
    static validateRequired(value) {
        return value.trim().length > 0;
    }
    
    static validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;
        
        let isValid = true;
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            if (input.required && !this.validateRequired(input.value)) {
                this.showError(input, 'This field is required');
                isValid = false;
            } else if (input.type === 'email' && input.value && !this.validateEmail(input.value)) {
                this.showError(input, 'Please enter a valid email');
                isValid = false;
            } else if (input.name === 'password' && input.value && !this.validatePassword(input.value)) {
                this.showError(input, 'Password must be at least 8 characters');
                isValid = false;
            } else {
                this.clearError(input);
            }
        });
        
        return isValid;
    }
    
    static showError(input, message) {
        const formGroup = input.closest('.form-group');
        if (formGroup) {
            let errorEl = formGroup.querySelector('.form-error');
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'form-error';
                formGroup.appendChild(errorEl);
            }
            errorEl.textContent = message;
            input.style.borderColor = 'var(--danger-color)';
        }
    }
    
    static clearError(input) {
        const formGroup = input.closest('.form-group');
        if (formGroup) {
            const errorEl = formGroup.querySelector('.form-error');
            if (errorEl) {
                errorEl.remove();
            }
            input.style.borderColor = '';
        }
    }
}

/**
 * File Upload Handler
 */

class FileUploadHandler {
    static handleFileInput(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
        if (!input) return;
        
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    if (preview.tagName === 'IMG') {
                        preview.src = event.target.result;
                    } else {
                        preview.innerHTML = `<img src="${event.target.result}" style="max-width: 100%; border-radius: var(--radius-md);">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * Utility Functions
 */

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateTime) {
    return new Date(dateTime).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDistanceToNow(date) {
    const now = new Date();
    const then = new Date(date);
    const seconds = Math.floor((now - then) / 1000);
    
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    const weeks = Math.floor(days / 7);
    if (weeks < 4) return `${weeks}w ago`;
    
    return formatDate(date);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="btn-close" onclick="this.parentElement.remove();">&times;</button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(notification, container.firstChild);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Fetch Wrapper
 */

async function fetchAPI(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'An error occurred');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * Bottom Navigation Active State
 */

function setActiveBottomNav(itemId) {
    document.querySelectorAll('.bottom-nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const activeItem = document.getElementById(itemId);
    if (activeItem) {
        activeItem.classList.add('active');
    }
}

