class JTooltip {
    constructor(options = {}) {
        this.options = {
            element: null,
            text: '',
            position: 'top',
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const positionClass = `j-tooltip-${this.options.position}`;
        
        this.options.element.innerHTML = `
            <span class="j-tooltip ${positionClass}">
                <span class="j-tooltip-trigger">${this.options.element.innerHTML}</span>
                <span class="j-tooltip-content">${this.options.text}</span>
            </span>
        `;
    }
    
    setText(text) {
        this.options.text = text;
        this.render();
    }
    
    setPosition(position) {
        this.options.position = position;
        this.render();
    }
}
