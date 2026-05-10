class JBadge {
    constructor(options = {}) {
        this.options = {
            element: null,
            text: '',
            type: 'primary',
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        this.options.element.innerHTML = `
            <span class="j-badge j-badge-${this.options.type}">${this.options.text}</span>
        `;
    }
    
    setText(text) {
        this.options.text = text;
        this.render();
    }
    
    setType(type) {
        this.options.type = type;
        this.render();
    }
}
