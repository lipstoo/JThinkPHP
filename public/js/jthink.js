const JThink = {
    modal: (options = {}) => {
        const defaults = {
            title: '',
            content: '',
            okText: '确定',
            cancelText: '取消',
            onOk: () => {},
            onCancel: () => {}
        };
        
        const opt = {...defaults, ...options};
        
        const modal = document.createElement('div');
        modal.className = 'j-modal';
        modal.innerHTML = `
            <div class="j-modal-content">
                <div class="j-modal-header">
                    <h3 class="j-modal-title">${opt.title}</h3>
                    <button class="j-modal-close" onclick="JThink.closeModal()">&times;</button>
                </div>
                <div class="j-modal-body">${opt.content}</div>
                <div class="j-modal-footer" style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                    <button class="j-btn j-btn-secondary" onclick="JThink.closeModal()">${opt.cancelText}</button>
                    <button class="j-btn j-btn-primary" onclick="JThink.closeModal();opt.onOk()">${opt.okText}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        modal.addEventListener('click', (e) => {
            if(e.target === modal) JThink.closeModal();
        });
        
        return modal;
    },
    
    closeModal: () => {
        const modal = document.querySelector('.j-modal');
        if(modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    },
    
    toast: (msg, type = 'info') => {
        const toast = document.createElement('div');
        toast.className = `j-alert j-alert-${type}`;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.maxWidth = '320px';
        toast.innerHTML = msg;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },
    
    confirm: (options) => {
        return new Promise((resolve) => {
            JThink.modal({
                ...options,
                onOk: () => resolve(true),
                onCancel: () => resolve(false)
            });
        });
    },
    
    ajax: (options = {}) => {
        const defaults = {
            url: '',
            method: 'GET',
            data: null,
            headers: {},
            success: () => {},
            error: () => {}
        };
        
        const opt = {...defaults, ...options};
        
        const xhr = new XMLHttpRequest();
        xhr.open(opt.method, opt.url);
        
        for(const [key, value] of Object.entries(opt.headers)) {
            xhr.setRequestHeader(key, value);
        }
        
        if(opt.method === 'POST' && !(opt.data instanceof FormData)) {
            xhr.setRequestHeader('Content-Type', 'application/json');
            opt.data = JSON.stringify(opt.data);
        }
        
        xhr.onload = () => {
            if(xhr.status >= 200 && xhr.status < 300) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    opt.success(res);
                } catch {
                    opt.success(xhr.responseText);
                }
            } else {
                opt.error(xhr.status, xhr.statusText);
            }
        };
        
        xhr.onerror = () => {
            opt.error('network', 'Network error');
        };
        
        xhr.send(opt.data);
        
        return xhr;
    },
    
    form: {
        serialize: (form) => {
            const data = new FormData(form);
            const obj = {};
            data.forEach((value, key) => {
                obj[key] = value;
            });
            return obj;
        },
        
        validate: (form, rules = {}) => {
            const data = JThink.form.serialize(form);
            const errors = [];
            
            for(const [key, rule] of Object.entries(rules)) {
                const value = data[key];
                
                if(rule.required && (!value || value.trim() === '')) {
                    errors.push(rule.message || `${key} is required`);
                    continue;
                }
                
                if(rule.min && value.length < rule.min) {
                    errors.push(rule.message || `${key} must be at least ${rule.min} characters`);
                }
                
                if(rule.max && value.length > rule.max) {
                    errors.push(rule.message || `${key} must be at most ${rule.max} characters`);
                }
                
                if(rule.pattern && !rule.pattern.test(value)) {
                    errors.push(rule.message || `${key} is invalid`);
                }
            }
            
            return errors;
        }
    },
    
    dom: {
        on: (el, event, handler) => {
            el.addEventListener(event, handler);
        },
        
        off: (el, event, handler) => {
            el.removeEventListener(event, handler);
        },
        
        toggleClass: (el, className) => {
            el.classList.toggle(className);
        },
        
        hasClass: (el, className) => {
            return el.classList.contains(className);
        },
        
        show: (el) => {
            el.style.display = '';
        },
        
        hide: (el) => {
            el.style.display = 'none';
        }
    },
    
    debounce: (fn, delay = 300) => {
        let timer = null;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    },
    
    throttle: (fn, delay = 300) => {
        let last = 0;
        return (...args) => {
            const now = Date.now();
            if(now - last >= delay) {
                last = now;
                fn(...args);
            }
        };
    },
    
    format: {
        date: (date, format = 'YYYY-MM-DD') => {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');
            
            return format
                .replace('YYYY', year)
                .replace('MM', month)
                .replace('DD', day)
                .replace('HH', hours)
                .replace('mm', minutes)
                .replace('ss', seconds);
        },
        
        number: (num, decimals = 0) => {
            return Number(num).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }
};

if(typeof module !== 'undefined') {
    module.exports = JThink;
}
