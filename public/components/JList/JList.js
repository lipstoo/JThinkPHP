class JList {
    constructor(options = {}) {
        this.options = {
            element: null,
            items: [],
            activeIndex: -1,
            onClick: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const itemsHtml = this.options.items.map((item, index) => {
            const activeClass = index === this.options.activeIndex ? 'active' : '';
            const disabledClass = item.disabled ? 'disabled' : '';
            return `
                <li class="j-list-item ${activeClass} ${disabledClass}" data-index="${index}">
                    ${item.icon || ''}
                    <span>${item.text}</span>
                </li>
            `;
        }).join('');
        
        this.options.element.innerHTML = `
            <ul class="j-list">${itemsHtml}</ul>
        `;
        
        this.options.element.querySelectorAll('.j-list-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                if (!item.classList.contains('disabled')) {
                    this.options.activeIndex = index;
                    this.options.onClick(index, this.options.items[index]);
                    this.render();
                }
            });
        });
    }
    
    addItem(item) {
        this.options.items.push(item);
        this.render();
    }
    
    removeItem(index) {
        this.options.items.splice(index, 1);
        this.render();
    }
    
    setActiveIndex(index) {
        this.options.activeIndex = index;
        this.render();
    }
}
