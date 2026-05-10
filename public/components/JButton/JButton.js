class JButton {
    constructor(options = {}) {
        this.options = {
            element: null,
            text: 'Button',
            type: 'primary',
            size: '',
            icon: null,
            iconPosition: 'left',
            onClick: () => {},
            disabled: false,
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const sizeClass = this.options.size ? `j-btn-${this.options.size}` : '';
        const iconHtml = this.options.icon ? this.renderIcon() : '';
        
        this.options.element.innerHTML = `
            <button class="j-btn j-btn-${this.options.type} ${sizeClass}" ${this.options.disabled ? 'disabled' : ''}>
                ${this.options.iconPosition === 'left' ? iconHtml : ''}
                <span>${this.options.text}</span>
                ${this.options.iconPosition === 'right' ? iconHtml : ''}
            </button>
        `;
        
        this.options.element.querySelector('.j-btn').addEventListener('click', (e) => {
            if (!this.options.disabled) {
                this.options.onClick(e);
            }
        });
    }
    
    renderIcon() {
        const iconPath = this.options.icon.startsWith('#') || this.options.icon.startsWith('http') 
            ? this.options.icon 
            : `/public/svg/icons.svg#icon-${this.options.icon}`;
        
        return `<svg class="j-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <use href="${iconPath}"/>
        </svg>`;
    }
    
    setText(text) {
        this.options.text = text;
        this.render();
    }
    
    setIcon(icon) {
        this.options.icon = icon;
        this.render();
    }
    
    setDisabled(disabled) {
        this.options.disabled = disabled;
        this.render();
    }
    
    static create(text, options = {}) {
        const button = document.createElement('button');
        const btn = new JButton({ element: button, text, ...options });
        return button;
    }
}
