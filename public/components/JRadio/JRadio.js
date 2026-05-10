class JRadio {
    constructor(options = {}) {
        this.options = {
            element: null,
            name: 'radio',
            label: '',
            value: '',
            checked: false,
            onChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        this.options.element.innerHTML = `
            <label class="j-radio-wrapper">
                <input type="radio" class="j-radio" name="${this.options.name}" value="${this.options.value}" ${this.options.checked ? 'checked' : ''}>
                <span>${this.options.label}</span>
            </label>
        `;
        
        this.options.element.querySelector('.j-radio').addEventListener('change', (e) => {
            if (e.target.checked) {
                this.options.checked = true;
                this.options.onChange(e.target.value);
            }
        });
    }
    
    isChecked() {
        return this.options.checked;
    }
    
    setChecked(checked) {
        this.options.checked = checked;
        this.render();
    }
}
