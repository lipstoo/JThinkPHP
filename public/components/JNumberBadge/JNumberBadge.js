class JNumberBadge {
    constructor(options = {}) {
        this.options = {
            element: null,
            number: 1,
            color: '',
            active: false,
            size: '',
            onClick: () => {},
            ...options
        };
        this.render();
    }

    render() {
        if (!this.options.element) return;

        const colorClass = this.options.color ? `j-number-badge-${this.options.color}` : '';
        const activeClass = this.options.active ? 'active' : '';
        const sizeClass = this.options.size ? `j-number-badge-${this.options.size}` : '';

        this.options.element.innerHTML = `
            <span class="j-number-badge ${colorClass} ${activeClass} ${sizeClass}">
                ${this.options.number}
            </span>
        `;

        this.options.element.querySelector('.j-number-badge').addEventListener('click', () => {
            this.options.onClick(this.options.number);
        });
    }

    setNumber(number) {
        this.options.number = number;
        this.render();
    }

    setActive(active) {
        this.options.active = active;
        this.render();
    }
}

class JNumberBadgeGroup {
    constructor(options = {}) {
        this.options = {
            element: null,
            numbers: [],
            activeIndex: -1,
            color: '',
            size: '',
            onChange: () => {},
            ...options
        };
        this.badgeElements = [];
        this.render();
    }

    render() {
        if (!this.options.element) return;

        let html = '<div class="j-number-badge-group">';

        this.options.numbers.forEach((number, index) => {
            const colorClass = this.options.color ? `j-number-badge-${this.options.color}` : '';
            const activeClass = index === this.options.activeIndex ? 'active' : '';
            const sizeClass = this.options.size ? `j-number-badge-${this.options.size}` : '';

            html += `
                <span class="j-number-badge ${colorClass} ${activeClass} ${sizeClass}" data-index="${index}">
                    ${number}
                </span>
            `;
        });

        html += '</div>';
        this.options.element.innerHTML = html;

        this.options.element.querySelectorAll('.j-number-badge').forEach((badge, index) => {
            badge.addEventListener('click', () => {
                this.options.activeIndex = index;
                this.options.onChange(this.options.numbers[index], index);
                this.render();
            });
        });
    }

    setActiveIndex(index) {
        this.options.activeIndex = index;
        this.render();
    }

    setNumbers(numbers) {
        this.options.numbers = numbers;
        this.render();
    }
}
