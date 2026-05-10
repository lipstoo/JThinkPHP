class JSelect {
    constructor(options = {}) {
        this.options = {
            element: null,
            label: '',
            value: '',
            options: [],
            onChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const labelHtml = this.options.label ? `<label class="j-label">${this.options.label}</label>` : '';
        const optionsHtml = this.options.options.map(opt => 
            `<option value="${opt.value}" ${this.options.value === opt.value ? 'selected' : ''}>${opt.label}</option>`
        ).join('');
        
        this.options.element.innerHTML = `
            <div class="j-select-wrapper">
                ${labelHtml}
                <select class="j-select">
                    ${optionsHtml}
                </select>
            </div>
        `;
        
        this.options.element.querySelector('.j-select').addEventListener('change', (e) => {
            this.options.value = e.target.value;
            this.options.onChange(e.target.value);
        });
    }
    
    getValue() {
        return this.options.value;
    }
    
    setValue(value) {
        this.options.value = value;
        this.render();
    }
    
    setOptions(options) {
        this.options.options = options;
        this.render();
    }
}
