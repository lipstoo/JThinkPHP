class JCard {
    constructor(options = {}) {
        this.options = {
            element: null,
            title: '',
            body: '',
            footer: '',
            ...options
        };
        
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const headerHtml = this.options.title ? `
            <div class="j-card-header">
                <h3 class="j-card-title">${this.options.title}</h3>
            </div>
        ` : '';
        
        const footerHtml = this.options.footer ? `
            <div class="j-card-footer">${this.options.footer}</div>
        ` : '';
        
        this.options.element.innerHTML = `
            <div class="j-card">
                ${headerHtml}
                <div class="j-card-body">${this.options.body}</div>
                ${footerHtml}
            </div>
        `;
    }
    
    setTitle(title) {
        this.options.title = title;
        this.render();
    }
    
    setBody(body) {
        this.options.body = body;
        this.render();
    }
    
    setFooter(footer) {
        this.options.footer = footer;
        this.render();
    }
}
