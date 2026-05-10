class JModal {
    constructor(options = {}) {
        this.options = {
            title: '',
            body: '',
            footer: '',
            okText: '确定',
            cancelText: '取消',
            onOk: () => {},
            onCancel: () => {},
            ...options
        };
        
        this.render();
    }
    
    render() {
        const overlay = document.createElement('div');
        overlay.className = 'j-modal-overlay';
        overlay.innerHTML = `
            <div class="j-modal">
                <div class="j-modal-header">
                    <h3 class="j-modal-title">${this.options.title}</h3>
                    <button class="j-modal-close">&times;</button>
                </div>
                <div class="j-modal-body">${this.options.body}</div>
                <div class="j-modal-footer">
                    <button class="j-btn j-btn-secondary">${this.options.cancelText}</button>
                    <button class="j-btn j-btn-primary">${this.options.okText}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        setTimeout(() => {
            overlay.classList.add('show');
        }, 10);
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.classList.contains('j-modal-close')) {
                this.close();
            }
        });
        
        overlay.querySelector('.j-btn-secondary').addEventListener('click', () => {
            this.options.onCancel();
            this.close();
        });
        
        overlay.querySelector('.j-btn-primary').addEventListener('click', () => {
            this.options.onOk();
            this.close();
        });
    }
    
    close() {
        const overlay = document.querySelector('.j-modal-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => {
                overlay.remove();
            }, 300);
        }
    }
}
