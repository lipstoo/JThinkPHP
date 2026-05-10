class JDataGrid {
    constructor(options = {}) {
        this.options = {
            element: null,
            columns: [],
            data: [],
            pageSize: 10,
            pageIndex: 1,
            allowSort: true,
            allowEdit: false,
            allowSelect: false,
            allowPagination: true,
            showFooter: false,
            title: '',
            toolbar: [],
            onRowClick: () => {},
            onCellEdit: () => {},
            onRowDelete: () => {},
            onRowEdit: () => {},
            onSelectionChange: () => {},
            ...options
        };

        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.selectedRows = [];
        this.render();
    }

    render() {
        if (!this.options.element) return;

        const html = `
            <div class="j-datagrid">
                ${this.renderHeader()}
                ${this.renderTable()}
                ${this.options.allowPagination ? this.renderPagination() : ''}
                ${this.options.showFooter ? this.renderFooter() : ''}
            </div>
        `;

        this.options.element.innerHTML = html;
        this.bindEvents();
    }

    renderHeader() {
        if (!this.options.title && this.options.toolbar.length === 0) return '';

        const toolbarHtml = this.options.toolbar.map(btn => `
            <button class="j-btn j-btn-sm ${btn.type || 'secondary'}" data-action="${btn.action}">
                ${btn.icon ? `<svg class="j-btn-icon"><use href="/public/svg/icons.svg#icon-${btn.icon}"></use></svg>` : ''}
                ${btn.text}
            </button>
        `).join('');

        return `
            <div class="j-datagrid-header">
                ${this.options.title ? `<h3 class="j-datagrid-title">${this.options.title}</h3>` : ''}
                ${toolbarHtml ? `<div class="j-datagrid-toolbar">${toolbarHtml}</div>` : ''}
            </div>
        `;
    }

    renderTable() {
        const data = this.getPagedData();

        if (data.length === 0) {
            return '<div class="j-datagrid-empty">暂无数据</div>';
        }

        const headerHtml = this.renderTableHeader();
        const bodyHtml = this.renderTableBody(data);

        return `
            <table class="j-datagrid-table">
                <thead>${headerHtml}</thead>
                <tbody>${bodyHtml}</tbody>
            </table>
        `;
    }

    renderTableHeader() {
        let html = '';

        if (this.options.allowSelect) {
            html += '<th><input type="checkbox" class="j-datagrid-checkbox j-datagrid-select-all"></th>';
        }

        this.options.columns.forEach(col => {
            const sortableClass = col.sortable !== false ? 'sortable' : '';
            const sortClass = this.sortColumn === col.field ? `sort-${this.sortDirection}` : '';
            html += `<th class="${sortableClass} ${sortClass}" data-field="${col.field}">${col.label}</th>`;
        });

        if (this.options.allowEdit || this.options.onRowDelete) {
            html += '<th style="width: 100px;">操作</th>';
        }

        return `<tr>${html}</tr>`;
    }

    renderTableBody(data) {
        return data.map((row, rowIndex) => {
            const selectedClass = this.selectedRows.includes(rowIndex) ? 'selected' : '';
            const actualIndex = (this.options.pageIndex - 1) * this.options.pageSize + rowIndex;

            let html = `<tr class="${selectedClass}" data-index="${actualIndex}">`;

            if (this.options.allowSelect) {
                const checked = this.selectedRows.includes(actualIndex) ? 'checked' : '';
                html += `<td class="j-datagrid-checkbox-cell"><input type="checkbox" class="j-datagrid-checkbox" ${checked}></td>`;
            }

            this.options.columns.forEach(col => {
                const value = typeof col.render === 'function' 
                    ? col.render(row, actualIndex) 
                    : row[col.field];

                if (col.editable) {
                    html += `<td class="j-datagrid-edit-cell" data-field="${col.field}">${value}</td>`;
                } else {
                    html += `<td>${value}</td>`;
                }
            });

            if (this.options.allowEdit || this.options.onRowDelete) {
                html += this.renderRowActions(actualIndex);
            }

            html += '</tr>';
            return html;
        }).join('');
    }

    renderRowActions(rowIndex) {
        const actions = [];

        if (this.options.allowEdit) {
            actions.push(`<button class="j-datagrid-action-btn" data-action="edit" data-row="${rowIndex}" title="编辑">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </button>`);
        }

        if (this.options.onRowDelete) {
            actions.push(`<button class="j-datagrid-action-btn" data-action="delete" data-row="${rowIndex}" title="删除">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>`);
        }

        return `<td><div class="j-datagrid-row-actions">${actions.join('')}</div></td>`;
    }

    renderPagination() {
        const totalPages = Math.ceil(this.options.data.length / this.options.pageSize);
        const current = this.options.pageIndex;

        let html = '<div class="j-datagrid-pagination">';

        html += `<span class="j-datagrid-pagination-info">
            显示 ${(current - 1) * this.options.pageSize + 1} - ${Math.min(current * this.options.pageSize, this.options.data.length)} 条，共 ${this.options.data.length} 条
        </span>`;

        html += '<div class="j-pagination">';
        
        html += `<button class="j-pagination-btn" ${current <= 1 ? 'disabled' : ''} data-page="prev">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="j-pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        html += `<button class="j-pagination-btn" ${current >= totalPages ? 'disabled' : ''} data-page="next">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>`;

        html += '</div></div>';

        return html;
    }

    renderFooter() {
        if (!this.options.footerRenderer) return '';
        
        return `
            <div class="j-datagrid-footer">
                ${this.options.footerRenderer(this.options.data)}
            </div>
        `;
    }

    bindEvents() {
        const grid = this.options.element;

        grid.querySelectorAll('.j-datagrid-table th.sortable').forEach(th => {
            th.addEventListener('click', () => {
                const field = th.dataset.field;
                if (this.sortColumn === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = field;
                    this.sortDirection = 'asc';
                }
                this.render();
            });
        });

        grid.querySelectorAll('.j-datagrid-table tbody tr').forEach(tr => {
            tr.addEventListener('click', (e) => {
                if (!e.target.closest('.j-datagrid-action-btn') && !e.target.closest('.j-datagrid-checkbox')) {
                    const index = parseInt(tr.dataset.index);
                    this.options.onRowClick(this.options.data[index], index);
                }
            });
        });

        grid.querySelectorAll('.j-datagrid-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.dataset.action;
                const rowIndex = parseInt(btn.dataset.row);
                const row = this.options.data[rowIndex];

                if (action === 'edit') {
                    this.options.onRowEdit(row, rowIndex);
                } else if (action === 'delete') {
                    this.options.onRowDelete(row, rowIndex);
                }
            });
        });

        grid.querySelectorAll('.j-pagination-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = btn.dataset.page;
                if (page === 'prev') {
                    this.options.pageIndex = Math.max(1, this.options.pageIndex - 1);
                } else if (page === 'next') {
                    const totalPages = Math.ceil(this.options.data.length / this.options.pageSize);
                    this.options.pageIndex = Math.min(totalPages, this.options.pageIndex + 1);
                } else {
                    this.options.pageIndex = parseInt(page);
                }
                this.render();
            });
        });

        const selectAll = grid.querySelector('.j-datagrid-select-all');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                const checked = e.target.checked;
                const checkboxes = grid.querySelectorAll('.j-datagrid-checkbox:not(.j-datagrid-select-all)');
                
                if (checked) {
                    this.selectedRows = Array.from({ length: this.options.data.length }, (_, i) => i);
                } else {
                    this.selectedRows = [];
                }

                checkboxes.forEach(cb => cb.checked = checked);
                this.options.onSelectionChange([...this.selectedRows]);
            });
        }

        grid.querySelectorAll('.j-datagrid-checkbox:not(.j-datagrid-select-all)').forEach((cb, index) => {
            cb.addEventListener('change', (e) => {
                const actualIndex = (this.options.pageIndex - 1) * this.options.pageSize + index;
                if (e.target.checked) {
                    if (!this.selectedRows.includes(actualIndex)) {
                        this.selectedRows.push(actualIndex);
                    }
                } else {
                    this.selectedRows = this.selectedRows.filter(i => i !== actualIndex);
                }
                this.options.onSelectionChange([...this.selectedRows]);
            });
        });

        grid.querySelectorAll('.j-datagrid-toolbar .j-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                this.options.onToolbarClick?.({ action, selectedRows: [...this.selectedRows] });
            });
        });
    }

    getPagedData() {
        let data = [...this.options.data];

        if (this.sortColumn && this.options.allowSort) {
            data.sort((a, b) => {
                const aVal = a[this.sortColumn];
                const bVal = b[this.sortColumn];
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        }

        const start = (this.options.pageIndex - 1) * this.options.pageSize;
        return data.slice(start, start + this.options.pageSize);
    }

    setData(data) {
        this.options.data = data;
        this.options.pageIndex = 1;
        this.render();
    }

    refresh() {
        this.render();
    }

    setPageSize(pageSize) {
        this.options.pageSize = pageSize;
        this.options.pageIndex = 1;
        this.render();
    }

    deleteRow(index) {
        this.options.data.splice(index, 1);
        this.selectedRows = this.selectedRows.filter(i => i !== index);
        this.selectedRows = this.selectedRows.map(i => i > index ? i - 1 : i);
        this.render();
    }

    updateRow(index, data) {
        this.options.data[index] = { ...this.options.data[index], ...data };
        this.render();
    }

    insertRow(data) {
        this.options.data.push(data);
        this.render();
    }

    getSelectedRows() {
        return this.selectedRows.map(i => this.options.data[i]);
    }

    getSelectedIndices() {
        return [...this.selectedRows];
    }
}
