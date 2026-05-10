class JTabs {
    constructor(options = {}) {
        this.options = {
            element: null,
            tabs: [],
            activeIndex: 0,
            onTabChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const tabsHtml = this.options.tabs.map((tab, index) => {
            const activeClass = index === this.options.activeIndex ? 'active' : '';
            return `<button class="j-tab-item ${activeClass}" data-index="${index}">${tab.label}</button>`;
        }).join('');
        
        const panelsHtml = this.options.tabs.map((tab, index) => {
            const activeClass = index === this.options.activeIndex ? 'active' : '';
            return `<div class="j-tab-panel ${activeClass}" data-index="${index}">${tab.content}</div>`;
        }).join('');
        
        this.options.element.innerHTML = `
            <div class="j-tabs">
                <div class="j-tabs-header">${tabsHtml}</div>
                <div class="j-tabs-content">${panelsHtml}</div>
            </div>
        `;
        
        this.options.element.querySelectorAll('.j-tab-item').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                this.setActive(index);
            });
        });
    }
    
    setActive(index) {
        if (index >= 0 && index < this.options.tabs.length) {
            this.options.activeIndex = index;
            this.options.onTabChange(index);
            this.render();
        }
    }
    
    getActiveIndex() {
        return this.options.activeIndex;
    }
}
