class JTable {
    constructor(options = {}) {
        this.options = {
            element: null,
            columns: [],
            data: [],
            striped: false,
            size: '',
            onRowClick: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const stripedClass = this.options.striped ? 'j-table-striped' : '';
        const sizeClass = this.options.size ? `j-table-${this.options.size}` : '';
        
        const headerHtml = this.options.columns.map(col => `<th>${col.label}</th>`).join('');
        
        const bodyHtml = this.options.data.map((row, index) => {
            const cells = this.options.columns.map(col => {
                const value = typeof col.render === 'function' ? col.render(row) : row[col.field];
                return `<td>${value}</td>`;
            }).join('');
            
            return `<tr data-index="${index}">${cells}</tr>`;
        }).join('');
        
        this.options.element.innerHTML = `
            <table class="j-table ${stripedClass} ${sizeClass}">
                <thead>
                    <tr>${headerHtml}</tr>
                </thead>
                <tbody>${bodyHtml}</tbody>
            </table>
        `;
        
        this.options.element.querySelectorAll('tbody tr').forEach((row, index) => {
            row.addEventListener('click', () => {
                this.options.onRowClick(index, this.options.data[index]);
            });
        });
    }
    
    setData(data) {
        this.options.data = data;
        this.render();
    }
    
    addRow(row) {
        this.options.data.push(row);
        this.render();
    }
    
    removeRow(index) {
        this.options.data.splice(index, 1);
        this.render();
    }
}
