class JIcon {
    constructor(options = {}) {
        this.options = {
            element: null,
            name: 'home',
            size: 'md',
            color: '',
            active: false,
            label: '',
            onClick: () => {},
            ...options
        };
        this.render();
    }

    render() {
        if (!this.options.element) return;

        const sizeClass = `j-icon-${this.options.size}`;
        const colorClass = this.options.color ? `j-icon-${this.options.color}` : '';
        const activeClass = this.options.active ? 'active' : '';

        if (this.options.label) {
            this.options.element.innerHTML = `
                <div class="j-icon-wrapper ${activeClass}">
                    <svg class="j-icon ${sizeClass} ${colorClass}">
                        <use href="/public/svg/icons.svg#icon-${this.options.name}"></use>
                    </svg>
                    <span class="j-icon-label">${this.options.label}</span>
                </div>
            `;
            this.options.element.querySelector('.j-icon-wrapper').addEventListener('click', () => {
                this.options.onClick();
            });
        } else {
            this.options.element.innerHTML = `
                <svg class="j-icon ${sizeClass} ${colorClass} ${activeClass}">
                    <use href="/public/svg/icons.svg#icon-${this.options.name}"></use>
                </svg>
            `;
        }
    }

    setName(name) {
        this.options.name = name;
        this.render();
    }

    setActive(active) {
        this.options.active = active;
        this.render();
    }

    setColor(color) {
        this.options.color = color;
        this.render();
    }
}
