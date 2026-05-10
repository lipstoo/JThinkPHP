class JPagination {
    constructor(options = {}) {
        this.options = {
            element: null,
            current: 1,
            total: 10,
            pageSize: 10,
            onPageChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const totalPages = Math.ceil(this.options.total / this.options.pageSize);
        const current = this.options.current;
        
        const html = [];
        
        html.push(`<button class="j-pagination-btn" ${current <= 1 ? 'disabled' : ''} data-page="prev">`);
        html.push('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>');
        html.push('</button>');
        
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) {
                html.push(`<button class="j-pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`);
            }
        } else {
            if (current <= 3) {
                for (let i = 1; i <= 4; i++) {
                    html.push(`<button class="j-pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`);
                }
                html.push('<span class="j-pagination-dots">...</span>');
                html.push(`<button class="j-pagination-btn" data-page="${totalPages}">${totalPages}</button>`);
            } else if (current >= totalPages - 2) {
                html.push(`<button class="j-pagination-btn" data-page="1">1</button>`);
                html.push('<span class="j-pagination-dots">...</span>');
                for (let i = totalPages - 3; i <= totalPages; i++) {
                    html.push(`<button class="j-pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`);
                }
            } else {
                html.push(`<button class="j-pagination-btn" data-page="1">1</button>`);
                html.push('<span class="j-pagination-dots">...</span>');
                for (let i = current - 1; i <= current + 1; i++) {
                    html.push(`<button class="j-pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`);
                }
                html.push('<span class="j-pagination-dots">...</span>');
                html.push(`<button class="j-pagination-btn" data-page="${totalPages}">${totalPages}</button>`);
            }
        }
        
        html.push(`<button class="j-pagination-btn" ${current >= totalPages ? 'disabled' : ''} data-page="next">`);
        html.push('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>');
        html.push('</button>');
        
        html.push(`<span class="j-pagination-info">${(current - 1) * this.options.pageSize + 1} - ${Math.min(current * this.options.pageSize, this.options.total)} of ${this.options.total}</span>`);
        
        this.options.element.innerHTML = `<nav class="j-pagination">${html.join('')}</nav>`;
        
        this.options.element.querySelectorAll('.j-pagination-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = btn.dataset.page;
                if (page === 'prev') {
                    this.goTo(current - 1);
                } else if (page === 'next') {
                    this.goTo(current + 1);
                } else {
                    this.goTo(parseInt(page));
                }
            });
        });
    }
    
    goTo(page) {
        const totalPages = Math.ceil(this.options.total / this.options.pageSize);
        if (page >= 1 && page <= totalPages && page !== this.options.current) {
            this.options.current = page;
            this.options.onPageChange(page);
            this.render();
        }
    }
    
    setCurrent(page) {
        this.options.current = page;
        this.render();
    }
    
    setTotal(total) {
        this.options.total = total;
        this.render();
    }
}
