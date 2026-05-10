class JShadowCard {
    constructor(options = {}) {
        this.options = {
            element: null,
            title: '',
            value: '',
            label: '',
            content: '',
            icon: '',
            iconColor: 'primary',
            footer: '',
            ...options
        };
        this.render();
    }

    render() {
        if (!this.options.element) return;

        const headerHtml = this.options.title ? `
            <div class="j-shadow-card-header">
                <h3 class="j-shadow-card-title">${this.options.title}</h3>
            </div>
        ` : '';

        const iconHtml = this.options.icon ? `
            <div class="j-icon-card j-icon-card-${this.options.iconColor} j-icon-card-md" style="float: right;">
                <svg class="j-icon j-icon-lg">
                    <use href="/public/svg/icons.svg#icon-${this.options.icon}"></use>
                </svg>
            </div>
        ` : '';

        const valueHtml = this.options.value ? `
            <div class="j-shadow-card-value">${this.options.value}</div>
            ${this.options.label ? `<div class="j-shadow-card-label">${this.options.label}</div>` : ''}
        ` : '';

        const contentHtml = this.options.content ? `
            <div class="j-shadow-card-content">${this.options.content}</div>
        ` : '';

        const footerHtml = this.options.footer ? `
            <div class="j-shadow-card-footer">${this.options.footer}</div>
        ` : '';

        this.options.element.innerHTML = `
            <div class="j-shadow-card">
                ${iconHtml}
                ${headerHtml}
                ${valueHtml}
                ${contentHtml}
                ${footerHtml}
            </div>
        `;
    }

    setValue(value) {
        this.options.value = value;
        this.render();
    }

    setTitle(title) {
        this.options.title = title;
        this.render();
    }
}
