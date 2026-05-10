class JProgress {
    constructor(options = {}) {
        this.options = {
            element: null,
            value: 0,
            max: 100,
            label: '',
            showLabel: true,
            type: '',
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const percentage = Math.min((this.options.value / this.options.max) * 100, 100);
        const typeClass = this.options.type ? `j-progress-${this.options.type}` : '';
        const labelHtml = this.options.showLabel ? `
            <div class="j-progress-label">
                <span>${this.options.label}</span>
                <span>${Math.round(percentage)}%</span>
            </div>
        ` : '';
        
        this.options.element.innerHTML = `
            <div class="j-progress-wrapper">
                ${labelHtml}
                <div class="j-progress ${typeClass}">
                    <div class="j-progress-bar" style="width: ${percentage}%"></div>
                </div>
            </div>
        `;
    }
    
    setValue(value) {
        this.options.value = value;
        this.render();
    }
    
    setLabel(label) {
        this.options.label = label;
        this.render();
    }
}
