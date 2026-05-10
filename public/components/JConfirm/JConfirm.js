class JConfirm {
    constructor(options = {}) {
        this.options = {
            title: '',
            subtitle: '',
            content: '',
            icon: 'alert-circle',
            iconType: 'danger',
            cancelText: '取消',
            confirmText: '确定',
            confirmType: 'danger',
            onConfirm: () => {},
            onCancel: () => {},
            ...options
        };

        this.render();
    }

    render() {
        const overlay = document.createElement('div');
        overlay.className = 'j-confirm-overlay';

        const iconTypeClass = `j-confirm-icon-${this.options.iconType}`;
        const confirmBtnClass = this.options.confirmType === 'danger' 
            ? 'j-confirm-btn-confirm' 
            : 'j-confirm-btn-confirm j-confirm-btn-confirm-primary';

        overlay.innerHTML = `
            <div class="j-confirm">
                <div class="j-confirm-icon ${iconTypeClass}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <use href="/public/svg/icons.svg#icon-${this.options.icon}"></use>
                    </svg>
                </div>
                ${this.options.title ? `<h3 class="j-confirm-title">${this.options.title}</h3>` : ''}
                ${this.options.subtitle ? `<h4 class="j-confirm-subtitle">${this.options.subtitle}</h4>` : ''}
                ${this.options.content ? `<p class="j-confirm-content">${this.options.content}</p>` : ''}
                <div class="j-confirm-footer">
                    <button class="j-confirm-btn j-confirm-btn-cancel">${this.options.cancelText}</button>
                    <button class="j-confirm-btn ${confirmBtnClass}">${this.options.confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        setTimeout(() => {
            overlay.classList.add('show');
        }, 10);

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.close();
                this.options.onCancel();
            }
        });

        overlay.querySelector('.j-confirm-btn-cancel').addEventListener('click', () => {
            this.close();
            this.options.onCancel();
        });

        overlay.querySelector('.j-confirm-btn-confirm, .j-confirm-btn-confirm-primary').addEventListener('click', () => {
            this.close();
            this.options.onConfirm();
        });
    }

    close() {
        const overlay = document.querySelector('.j-confirm-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => {
                overlay.remove();
            }, 300);
        }
    }

    setIcon(iconName) {
        this.options.icon = iconName;
        const iconEl = document.querySelector('.j-confirm-icon svg use');
        if (iconEl) {
            iconEl.setAttribute('href', `/public/svg/icons.svg#icon-${iconName}`);
        }
    }

    setIconType(type) {
        const iconEl = document.querySelector('.j-confirm-icon');
        if (iconEl) {
            iconEl.className = `j-confirm-icon j-confirm-icon-${type}`;
        }
        this.options.iconType = type;
    }

    setTitle(title) {
        const titleEl = document.querySelector('.j-confirm-title');
        if (titleEl) {
            titleEl.textContent = title;
        }
        this.options.title = title;
    }

    setContent(content) {
        const contentEl = document.querySelector('.j-confirm-content');
        if (contentEl) {
            contentEl.textContent = content;
        }
        this.options.content = content;
    }

    static danger(options) {
        return new JConfirm({
            icon: 'trash',
            iconType: 'danger',
            confirmType: 'danger',
            ...options
        });
    }

    static warning(options) {
        return new JConfirm({
            icon: 'alert-circle',
            iconType: 'warning',
            confirmType: 'danger',
            ...options
        });
    }

    static success(options) {
        return new JConfirm({
            icon: 'check-circle',
            iconType: 'success',
            confirmType: 'primary',
            ...options
        });
    }

    static info(options) {
        return new JConfirm({
            icon: 'info',
            iconType: 'info',
            confirmType: 'primary',
            ...options
        });
    }
}

class JToast {
    constructor(options = {}) {
        this.options = {
            message: '',
            type: 'info',
            duration: 3000,
            position: 'top-right',
            onClose: () => {},
            ...options
        };

        this.render();
    }

    render() {
        const toast = document.createElement('div');
        toast.className = `j-toast j-toast-${this.options.type} j-toast-${this.options.position}`;

        const icons = {
            success: 'check-circle',
            error: 'x-circle',
            warning: 'alert-circle',
            info: 'info'
        };

        toast.innerHTML = `
            <div class="j-toast-content">
                <svg class="j-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <use href="/public/svg/icons.svg#icon-${icons[this.options.type]}"></use>
                </svg>
                <span class="j-toast-message">${this.options.message}</span>
                <button class="j-toast-close">&times;</button>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        const close = () => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
                this.options.onClose();
            }, 300);
        };

        toast.querySelector('.j-toast-close').addEventListener('click', close);

        if (this.options.duration > 0) {
            setTimeout(close, this.options.duration);
        }
    }

    static success(message, options = {}) {
        return new JToast({ message, type: 'success', ...options });
    }

    static error(message, options = {}) {
        return new JToast({ message, type: 'error', ...options });
    }

    static warning(message, options = {}) {
        return new JToast({ message, type: 'warning', ...options });
    }

    static info(message, options = {}) {
        return new JToast({ message, type: 'info', ...options });
    }
}
