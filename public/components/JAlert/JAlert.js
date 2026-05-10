class JAlert {
    constructor(options = {}) {
        this.options = {
            element: null,
            type: 'info',
            message: '',
            dismissible: true,
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const icons = {
            info: '<svg class="j-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
            success: '<svg class="j-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            warning: '<svg class="j-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>',
            danger: '<svg class="j-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>'
        };
        
        const closeHtml = this.options.dismissible ? '<button class="j-alert-close">&times;</button>' : '';
        
        this.options.element.innerHTML = `
            <div class="j-alert j-alert-${this.options.type}">
                ${icons[this.options.type]}
                <span>${this.options.message}</span>
                ${closeHtml}
            </div>
        `;
        
        if (this.options.dismissible) {
            this.options.element.querySelector('.j-alert-close').addEventListener('click', () => {
                this.hide();
            });
        }
    }
    
    hide() {
        this.options.element.style.opacity = '0';
        this.options.element.style.transform = 'translateY(-10px)';
        this.options.element.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            this.options.element.style.display = 'none';
        }, 300);
    }
    
    show() {
        this.options.element.style.display = '';
        this.options.element.style.opacity = '1';
        this.options.element.style.transform = 'translateY(0)';
    }
    
    setMessage(message) {
        this.options.message = message;
        this.render();
    }
}
