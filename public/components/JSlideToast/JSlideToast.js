class JSlideToast {
    constructor(options = {}) {
        this.options = {
            title: '',
            message: '',
            type: 'success',
            duration: 3500,
            ...options
        };

        this.render();
    }

    render() {
        const icons = {
            success: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
            error: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
            warning: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`,
            info: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
            add: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>`,
            edit: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`
        };

        const id = 'jst-' + Date.now();
        const type = this.options.type;
        const icon = icons[type] || icons.success;

        const html = `
            <div class="j-slide-toast ${type}" id="${id}">
                <div class="j-slide-toast-side-bar"></div>
                <div class="j-slide-toast-icon-box">${icon}</div>
                <div class="j-slide-toast-content">
                    <h4 class="j-slide-toast-title">${this.options.title}</h4>
                    <p class="j-slide-toast-message">${this.options.message}</p>
                </div>
            </div>`;

        let container = document.querySelector('.j-slide-toast-container');
        if (!container) {
            document.body.insertAdjacentHTML('beforeend', '<div class="j-slide-toast-container"></div>');
            container = document.querySelector('.j-slide-toast-container');
        }
        container.insertAdjacentHTML('beforeend', html);

        const el = document.getElementById(id);

        setTimeout(() => {
            el.classList.add('j-slide-toast-leave');
            setTimeout(() => {
                el.remove();
            }, 400);
        }, this.options.duration);
    }

    static success(title, message, duration = 3500) {
        return new JSlideToast({ title, message, type: 'success', duration });
    }

    static error(title, message, duration = 3500) {
        return new JSlideToast({ title, message, type: 'error', duration });
    }

    static warning(title, message, duration = 3500) {
        return new JSlideToast({ title, message, type: 'warning', duration });
    }

    static info(title, message, duration = 3500) {
        return new JSlideToast({ title, message, type: 'info', duration });
    }

    static add(title, message, duration = 3500) {
        return new JSlideToast({ title, message, type: 'add', duration });
    }

    static edit(title, message, duration = 3500) {
        return new JSlideToast({ title, message, type: 'edit', duration });
    }
}
