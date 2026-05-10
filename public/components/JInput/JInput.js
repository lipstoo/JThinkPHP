class JInput {
    constructor(options = {}) {
        this.options = {
            element: null,
            type: 'text',
            placeholder: '',
            value: '',
            label: '',
            error: '',
            success: '',
            onChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const errorClass = this.options.error ? 'j-input-error' : '';
        const successClass = this.options.success ? 'j-input-success' : '';
        const labelHtml = this.options.label ? `<label class="j-label">${this.options.label}</label>` : '';
        const errorHtml = this.options.error ? `<div class="j-input-error-text">${this.options.error}</div>` : '';
        
        this.options.element.innerHTML = `
            <div class="j-input-wrapper">
                ${labelHtml}
                <input 
                    type="${this.options.type}" 
                    class="j-input ${errorClass} ${successClass}" 
                    placeholder="${this.options.placeholder}"
                    value="${this.options.value}"
                >
                ${errorHtml}
            </div>
        `;
        
        this.options.element.querySelector('.j-input').addEventListener('input', (e) => {
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
    
    setError(error) {
        this.options.error = error;
        this.options.success = '';
        this.render();
    }
    
    setSuccess(success) {
        this.options.success = success;
        this.options.error = '';
        this.render();
    }
}
