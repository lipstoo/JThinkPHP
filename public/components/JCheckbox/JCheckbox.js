class JCheckbox {
    constructor(options = {}) {
        this.options = {
            element: null,
            label: '',
            checked: false,
            onChange: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        this.options.element.innerHTML = `
            <label class="j-checkbox-wrapper">
                <input type="checkbox" class="j-checkbox" ${this.options.checked ? 'checked' : ''}>
                <span>${this.options.label}</span>
            </label>
        `;
        
        this.options.element.querySelector('.j-checkbox').addEventListener('change', (e) => {
            this.options.checked = e.target.checked;
            this.options.onChange(e.target.checked);
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
