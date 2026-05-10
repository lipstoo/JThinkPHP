class JAvatar {
    constructor(options = {}) {
        this.options = {
            element: null,
            initials: 'JD',
            size: '',
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const sizeClass = this.options.size ? `j-avatar-${this.options.size}` : '';
        
        this.options.element.innerHTML = `
            <div class="j-avatar ${sizeClass}">${this.options.initials}</div>
        `;
    }
    
    setInitials(initials) {
        this.options.initials = initials;
        this.render();
    }
    
    setSize(size) {
        this.options.size = size;
        this.render();
    }
}
