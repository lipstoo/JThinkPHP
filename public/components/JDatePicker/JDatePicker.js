class JDatePicker {
    constructor(options = {}) {
        this.options = {
            element: null,
            value: '',
            label: '',
            onChange: () => {},
            ...options
        };
        
        this.currentDate = this.options.value ? new Date(this.options.value) : new Date();
        this.render();
    }
    
    render() {
        if (!this.options.element) return;
        
        const labelHtml = this.options.label ? `<label class="j-label">${this.options.label}</label>` : '';
        
        this.options.element.innerHTML = `
            <div class="j-datepicker">
                ${labelHtml}
                <input type="text" class="j-datepicker-input" readonly value="${this.formatDate(this.options.value)}">
                <div class="j-datepicker-calendar">
                    <div class="j-datepicker-header">
                        <button class="j-datepicker-nav" data-nav="prev">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span>${this.currentDate.getFullYear()}年${this.currentDate.getMonth() + 1}月</span>
                        <button class="j-datepicker-nav" data-nav="next">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                    <div class="j-datepicker-weekdays">
                        <span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span>
                    </div>
                    <div class="j-datepicker-days"></div>
                </div>
            </div>
        `;
        
        this.renderDays();
        
        this.options.element.querySelector('.j-datepicker-input').addEventListener('click', () => {
            this.toggleCalendar();
        });
        
        this.options.element.querySelectorAll('.j-datepicker-nav').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const nav = e.target.closest('.j-datepicker-nav').dataset.nav;
                if (nav === 'prev') {
                    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                } else {
                    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                }
                this.render();
            });
        });
        
        document.addEventListener('click', (e) => {
            if (!this.options.element.contains(e.target)) {
                this.hideCalendar();
            }
        });
    }
    
    renderDays() {
        const daysContainer = this.options.element.querySelector('.j-datepicker-days');
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        let html = '';
        
        for (let i = firstDay - 1; i >= 0; i--) {
            html += `<button class="j-datepicker-day other-month">${daysInPrevMonth - i}</button>`;
        }
        
        for (let i = 1; i <= daysInMonth; i++) {
            const isToday = this.isToday(year, month, i);
            const isSelected = this.isSelected(year, month, i);
            const todayClass = isToday ? 'today' : '';
            const activeClass = isSelected ? 'active' : '';
            
            html += `<button class="j-datepicker-day ${todayClass} ${activeClass}" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}">${i}</button>`;
        }
        
        const remainingDays = 42 - (firstDay + daysInMonth);
        for (let i = 1; i <= remainingDays; i++) {
            html += `<button class="j-datepicker-day other-month">${i}</button>`;
        }
        
        daysContainer.innerHTML = html;
        
        daysContainer.querySelectorAll('.j-datepicker-day').forEach(day => {
            if (!day.classList.contains('other-month')) {
                day.addEventListener('click', (e) => {
                    const date = e.target.dataset.date;
                    this.options.value = date;
                    this.options.onChange(date);
                    this.options.element.querySelector('.j-datepicker-input').value = this.formatDate(date);
                    this.hideCalendar();
                });
            }
        });
    }
    
    isToday(year, month, day) {
        const today = new Date();
        return today.getFullYear() === year && today.getMonth() === month && today.getDate() === day;
    }
    
    isSelected(year, month, day) {
        if (!this.options.value) return false;
        const selected = new Date(this.options.value);
        return selected.getFullYear() === year && selected.getMonth() === month && selected.getDate() === day;
    }
    
    formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }
    
    toggleCalendar() {
        const calendar = this.options.element.querySelector('.j-datepicker-calendar');
        calendar.classList.toggle('show');
    }
    
    hideCalendar() {
        const calendar = this.options.element.querySelector('.j-datepicker-calendar');
        calendar.classList.remove('show');
    }
    
    getValue() {
        return this.options.value;
    }
    
    setValue(value) {
        this.options.value = value;
        this.currentDate = new Date(value);
        this.render();
    }
}
