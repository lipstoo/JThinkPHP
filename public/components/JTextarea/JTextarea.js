class JTextarea {
    constructor(options = {}) {
        this.options = {
            element: null,
            placeholder: '',
            value: '',
            label: '',
            rows: 4,
            onChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const labelHtml = this.options.label ? `<label class="j-label">${this.options.label}</label>` : '';
        
        this.options.element.innerHTML = `
            <div class="j-textarea-wrapper">
                ${labelHtml}
                <textarea 
                    class="j-textarea" 
                    placeholder="${this.options.placeholder}"
                    rows="${this.options.rows}"
                >${this.options.value}</textarea>
            </div>
        `;
        
        this.options.element.querySelector('.j-textarea').addEventListener('input', (e) => {
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
}
