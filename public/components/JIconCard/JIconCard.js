class JIconCard {
    constructor(options = {}) {
        this.options = {
            element: null,
            icon: 'home',
            color: 'primary',
            size: 'md',
            onClick: () => {},
            ...options
        };
        this.render();
    }

    render() {
        if (!this.options.element) return;

        const sizeClass = `j-icon-card-${this.options.size}`;
        const colorClass = `j-icon-card-${this.options.color}`;
        const iconSize = this.options.size === 'sm' ? 'md' : 
                         this.options.size === 'lg' ? 'xl' : 
                         this.options.size === 'xl' ? 'xl' : 'lg';

        this.options.element.innerHTML = `
            <div class="j-icon-card ${sizeClass} ${colorClass}">
                <svg class="j-icon j-icon-${iconSize}">
                    <use href="/public/svg/icons.svg#icon-${this.options.icon}"></use>
                </svg>
            </div>
        `;

        this.options.element.querySelector('.j-icon-card').addEventListener('click', () => {
            this.options.onClick();
        });
    }

    setIcon(icon) {
        this.options.icon = icon;
        this.render();
    }

    setColor(color) {
        this.options.color = color;
        this.render();
    }
}
