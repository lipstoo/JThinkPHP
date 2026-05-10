class JActionButtons {
    constructor(options = {}) {
        this.options = {
            element: null,
            actions: [],
            showGroup: true,
            size: 'md',
            ...options
        };

        this.render();
    }

    render() {
        if (!this.options.element) return;

        const actionsHtml = this.options.actions.map(action => {
            const icon = this.getActionIcon(action.type);
            const btnClass = this.getActionClass(action.type);
            const text = action.text || this.getActionText(action.type);

            return `
                <button class="j-action-btn ${btnClass}" data-action="${action.type}" title="${text}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <use href="/public/svg/icons.svg#icon-${icon}"/>
                    </svg>
                    <span>${text}</span>
                </button>
            `;
        }).join('');

        const containerClass = this.options.showGroup ? 'j-action-btn-group' : 'j-action-buttons';

        this.options.element.innerHTML = `
            <div class="${containerClass}">${actionsHtml}</div>
        `;

        this.bindEvents();
    }

    getActionIcon(type) {
        const icons = {
            add: 'plus',
            create: 'plus',
            insert: 'plus',
            edit: 'edit-2',
            update: 'edit-2',
            modify: 'edit-2',
            delete: 'trash-2',
            remove: 'trash-2',
            view: 'eye',
            preview: 'eye',
            detail: 'eye',
            show: 'eye',
            search: 'search',
            find: 'search',
            query: 'search',
            list: 'list',
            table: 'list',
            grid: 'layout-grid',
            refresh: 'refresh-cw',
            reload: 'refresh-cw',
            export: 'download',
            import: 'upload',
            print: 'printer',
            share: 'share-2',
            copy: 'copy',
            save: 'save',
            submit: 'send',
            cancel: 'x',
            close: 'x',
            back: 'arrow-left',
            prev: 'arrow-left',
            next: 'arrow-right',
            up: 'arrow-up',
            down: 'arrow-down',
            settings: 'settings',
            config: 'settings',
            options: 'settings',
            permission: 'shield',
            auth: 'shield',
            role: 'users',
            user: 'user',
            group: 'users',
            help: 'help-circle',
            info: 'info',
            warning: 'alert-circle',
            success: 'check-circle',
            error: 'x-circle',
            approve: 'check',
            reject: 'x',
            enable: 'check',
            disable: 'x',
            lock: 'lock',
            unlock: 'unlock',
            star: 'star',
            favorite: 'star',
            bookmark: 'bookmark',
            tag: 'tag',
            filter: 'filter',
            sort: 'arrow-up-down',
            expand: 'chevron-down',
            collapse: 'chevron-up',
            link: 'link',
            email: 'mail',
            phone: 'phone',
            message: 'message-circle',
            notification: 'bell',
            history: 'history',
            calendar: 'calendar',
            clock: 'clock',
            file: 'file',
            folder: 'folder',
            image: 'image',
            video: 'video',
            audio: 'music',
            map: 'map',
            location: 'map-pin',
            globe: 'globe',
            cloud: 'cloud',
            database: 'database',
            server: 'server',
            terminal: 'terminal',
            code: 'code',
            git: 'git-branch',
            github: 'git-commit',
            home: 'home',
            dashboard: 'layout-dashboard',
            menu: 'menu',
            bars: 'menu'
        };

        return icons[type] || 'circle';
    }

    getActionClass(type) {
        const classes = {
            add: 'j-action-btn-primary',
            create: 'j-action-btn-primary',
            insert: 'j-action-btn-primary',
            edit: '',
            update: '',
            modify: '',
            delete: 'j-action-btn-danger',
            remove: 'j-action-btn-danger',
            view: '',
            preview: '',
            detail: '',
            show: '',
            search: '',
            find: '',
            query: '',
            permission: 'j-action-btn-info',
            auth: 'j-action-btn-info',
            role: '',
            user: '',
            group: '',
            approve: 'j-action-btn-success',
            reject: 'j-action-btn-danger',
            enable: 'j-action-btn-success',
            disable: 'j-action-btn-warning',
            lock: 'j-action-btn-warning',
            unlock: 'j-action-btn-success',
            export: '',
            import: '',
            refresh: '',
            settings: '',
            config: '',
            options: '',
            save: 'j-action-btn-primary',
            submit: 'j-action-btn-primary',
            cancel: '',
            close: '',
            back: '',
            prev: '',
            next: '',
            warning: 'j-action-btn-warning',
            error: 'j-action-btn-danger',
            success: 'j-action-btn-success',
            info: 'j-action-btn-info'
        };

        return classes[type] || '';
    }

    getActionText(type) {
        const texts = {
            add: '新增',
            create: '创建',
            insert: '插入',
            edit: '编辑',
            update: '更新',
            modify: '修改',
            delete: '删除',
            remove: '移除',
            view: '查看',
            preview: '预览',
            detail: '详情',
            show: '显示',
            search: '搜索',
            find: '查找',
            query: '查询',
            list: '列表',
            table: '表格',
            grid: '网格',
            refresh: '刷新',
            reload: '重新加载',
            export: '导出',
            import: '导入',
            print: '打印',
            share: '分享',
            copy: '复制',
            save: '保存',
            submit: '提交',
            cancel: '取消',
            close: '关闭',
            back: '返回',
            prev: '上一页',
            next: '下一页',
            up: '向上',
            down: '向下',
            settings: '设置',
            config: '配置',
            options: '选项',
            permission: '权限',
            auth: '认证',
            role: '角色',
            user: '用户',
            group: '组',
            help: '帮助',
            info: '信息',
            warning: '警告',
            success: '成功',
            error: '错误',
            approve: '通过',
            reject: '拒绝',
            enable: '启用',
            disable: '禁用',
            lock: '锁定',
            unlock: '解锁',
            star: '收藏',
            favorite: '收藏',
            bookmark: '书签',
            tag: '标签',
            filter: '筛选',
            sort: '排序',
            expand: '展开',
            collapse: '收起',
            link: '链接',
            email: '邮件',
            phone: '电话',
            message: '消息',
            notification: '通知',
            history: '历史',
            calendar: '日历',
            clock: '时间',
            file: '文件',
            folder: '文件夹',
            image: '图片',
            video: '视频',
            audio: '音频',
            map: '地图',
            location: '位置',
            globe: '全球',
            cloud: '云',
            database: '数据库',
            server: '服务器',
            terminal: '终端',
            code: '代码',
            git: '版本',
            github: '提交',
            home: '首页',
            dashboard: '仪表盘',
            menu: '菜单',
            bars: '菜单'
        };

        return texts[type] || type;
    }

    bindEvents() {
        this.options.element.querySelectorAll('.j-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.dataset.action;
                const rowData = this.options.rowData;
                
                if (this.options.onAction) {
                    this.options.onAction(action, rowData);
                }
            });
        });
    }

    setRowData(data) {
        this.options.rowData = data;
    }

    updateActions(actions) {
        this.options.actions = actions;
        this.render();
    }

    static create(actions, options = {}) {
        const container = document.createElement('div');
        const actionButtons = new JActionButtons({ element: container, actions, ...options });
        return container;
    }
}
