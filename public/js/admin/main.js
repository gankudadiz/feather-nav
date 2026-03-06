function adminInit() {
    return {
        csrfToken: '',
        categories: [],
        links: [],
        auditLogs: [],
        selectedAuditAction: '',
        newCategory: '',
        newLink: {
            category_id: '',
            title: '',
            url: '',
            description: '',
            need_vpn: '0',
            icon: ''
        },

        // 标签页状态
        currentTab: 'statistics', // 默认显示"数据统计"标签

        // 编辑相关数据
        showEditCategoryModal: false,
        showEditLinkModal: false,
        editingCategory: { id: null, name: '' },
        editingLink: { id: null, category_id: '', title: '', url: '', description: '', need_vpn: '0', icon: '', sort_order: 0 },

        // 链接搜索和分页
        filteredLinks: [],
        paginatedLinks: [],
        linkSearchTerm: '',
        selectedCategory: '',
        filterNoIcon: false, // 新增：是否只看无图标链接
        currentPage: 1,
        perPage: 10,
        totalPages: 0,

        // 统计数据
        stats: {
            total_links: 0,
            vpn_links: 0,
            total_categories: 0,
            uncategorized_links: 0,
            no_icon_links: 0,
            vpn_percentage: 0
        },

        // 分类排序
        categoryUpdateTimer: null,

        // Toast 组件
        toast: {
            visible: false,
            message: '',
            type: 'success', // 'success' or 'error'
            progress: 100,
            remainingTime: 3000,
            timer: null
        },

        // 弹窗状态
        dialog: {
            visible: false,
            title: '',
            message: '',
            type: 'danger',
            resolve: null // 用于保存 Promise 的 resolve 函数
        },

        async init() {
            // 从 meta 标签中获取 csrfToken
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta) {
                this.csrfToken = tokenMeta.getAttribute('content');
            }

            // 处理 Hash 路由
            this.handleHashRoute();
            window.addEventListener('hashchange', () => this.handleHashRoute());

            // 监听 currentTab 变化并更新 Hash
            this.$watch('currentTab', value => {
                if (window.location.hash !== '#' + value) {
                    window.location.hash = value;
                }
            });
            
            await Promise.all([
                this.loadData(),
                this.loadStatistics()
            ]);
            this.filterLinks(); // 初始化时应用筛选
        },

        handleHashRoute() {
            const hash = window.location.hash.replace('#', '');
            const validTabs = ['statistics', 'links', 'addLink', 'categories', 'auditLogs'];
            if (hash && validTabs.includes(hash)) {
                this.currentTab = hash;
            } else if (!hash) {
                // 默认跳转到 statistics 并设置 hash
                window.location.hash = 'statistics';
            }
        },

        async loadData() {
            const [categoriesRes, linksRes] = await Promise.all([
                fetch('/api/categories'),
                fetch('/api/links')
            ]);
            this.categories = await categoriesRes.json();
            this.links = await linksRes.json();
            this.filteredLinks = [...this.links]; // 初始化筛选列表
        },

        async loadStatistics() {
            try {
                const res = await fetch('/api/statistics');
                if (res.ok) {
                    this.stats = await res.json();
                }
            } catch (e) {
                console.error('加载统计数据失败:', e);
            }
        },

        async addCategory() {
            try {
                const res = await fetch('/api/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({ name: this.newCategory })
                });
                if (res.ok) {
                    this.showToast('分类添加成功', 'success');
                    this.newCategory = '';
                    await this.loadData();
                    this.filterLinks();
                } else {
                    this.showToast('分类添加失败', 'error');
                }
            } catch (e) {
                this.showToast('分类添加失败：' + e.message, 'error');
            }
        },

        // 筛选链接
        filterLinks() {
            let filtered = this.links;

            if (this.linkSearchTerm) {
                const term = this.linkSearchTerm.toLowerCase();
                filtered = filtered.filter(link =>
                    link.title.toLowerCase().includes(term) ||
                    link.url.toLowerCase().includes(term) ||
                    (link.description || '').toLowerCase().includes(term)
                );
            }

            if (this.selectedCategory) {
                filtered = filtered.filter(link =>
                    link.category_id == this.selectedCategory
                );
            }

            if (this.filterNoIcon) {
                filtered = filtered.filter(link => !link.icon || link.icon === '');
            }

            this.filteredLinks = filtered;
            this.currentPage = 1; // 重置到第一页
            this.updatePaginatedLinks();
        },

        // 更新分页数据
        updatePaginatedLinks() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            this.paginatedLinks = this.filteredLinks.slice(start, end);
            this.totalPages = Math.ceil(this.filteredLinks.length / this.perPage);
        },

        // 切换页面
        changePage(page) {
            if (page < 1 || page > Math.ceil(this.filteredLinks.length / this.perPage)) return;
            this.currentPage = page;
            this.updatePaginatedLinks();
        },

        // 移动分类
        async moveCategory(id, direction) {
            const index = this.categories.findIndex(c => c.id === id);
            const newIndex = index + direction;

            if (newIndex < 0 || newIndex >= this.categories.length) return;

            // 交换位置
            [this.categories[index], this.categories[newIndex]] =
                [this.categories[newIndex], this.categories[index]];

            // 重新分配排序值
            this.categories.forEach((cat, i) => {
                cat.sort_order = i + 1;
            });

            // 延迟更新，避免频繁请求
            clearTimeout(this.categoryUpdateTimer);
            this.categoryUpdateTimer = setTimeout(() => {
                this.batchUpdateCategoryOrder();
            }, 500);
        },

        // 批量更新分类排序
        async batchUpdateCategoryOrder() {
            const updates = this.categories.map(cat => ({
                id: cat.id,
                sort_order: cat.sort_order
            }));

            try {
                await fetch('/api/categories/batch-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({ updates })
                });
            } catch (e) {
                console.error('更新排序失败:', e);
            }
        },

        async deleteCategory(id) {
            // 前端：统计有多少链接会受到影响
            const linkCount = this.links.filter(link => link.category_id == id).length;

            let message = '确定要删除此分类吗？操作无法撤销。';
            if (linkCount > 0) {
                message = `该分类下有 ${linkCount} 个链接，删除后这些链接将被设为“未分类”状态，是否继续？`;
            }

            const confirmed = await this.showConfirm('删除分类', message, 'danger');
            if (!confirmed) return;

            try {
                const res = await fetch(`/api/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    }
                });

                const data = await res.json();

                if (res.ok) {
                    this.showToast('分类删除成功', 'success');
                    await this.loadData();
                    this.filterLinks();
                } else {
                    this.showToast(data.error || '分类删除失败', 'error');
                }
            } catch (e) {
                this.showToast('分类删除失败：' + e.message, 'error');
            }
        },

        async addLink() {
            try {
                const res = await fetch('/api/links', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(this.newLink)
                });

                if (!res.ok) {
                    this.showToast('链接添加失败', 'error');
                    return;
                }
                const data = await res.json();

                // 异步获取图标
                if (!this.newLink.icon) {
                    this.fetchIcon(data.id);
                }

                this.showToast('链接添加成功', 'success');
                this.newLink = { category_id: '', title: '', url: '', description: '', need_vpn: '0', icon: '' };
                this.currentTab = 'links';
                await this.loadData();
                this.filterLinks();
            } catch (e) {
                this.showToast('链接添加失败：' + e.message, 'error');
            }
        },

        async fetchIcon(id) {
            try {
                // 显示加载状态（可选，这里我们可以简单地依赖数据更新）
                await fetch(`/api/links/${id}/icon`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': this.csrfToken }
                });
                // 成功后重新加载列表以显示新图标
                await this.loadData();
            } catch (e) {
                console.error('Fetch icon failed:', e);
            }
        },

        async deleteLink(id) {
            const confirmed = await this.showConfirm('删除链接', '确定要删除这条链接吗？此操作无法撤销。', 'danger');
            if (!confirmed) return;
            try {
                const res = await fetch(`/api/links/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    }
                });
                if (res.ok) {
                    this.showToast('链接删除成功', 'success');
                    this.currentTab = 'links';
                    await this.loadData();
                    this.filterLinks();
                } else {
                    this.showToast('链接删除失败', 'error');
                }
            } catch (e) {
                this.showToast('链接删除失败：' + e.message, 'error');
            }
        },

        async uploadIcon(event, targetLink) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch('/api/upload/icon', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: formData
                });

                const data = await res.json();

                if (!res.ok) {
                    this.showToast(data.error || '图标上传失败', 'error');
                    return;
                }

                targetLink.icon = data.url;
                event.target.value = ''; // 重置文件选择框
            } catch (e) {
                console.error('Upload failed:', e);
                this.showToast('图标上传出错，请检查网络后重试', 'error');
            }
        },

        getCategoryName(id) {
            const cat = this.categories.find(c => c.id === id);
            return cat ? cat.name : '未分类';
        },

        // 编辑分类相关方法
        openEditCategoryModal(cat) {
            this.editingCategory = { id: cat.id, name: cat.name };
            this.showEditCategoryModal = true;
        },

        async updateCategory() {
            try {
                const res = await fetch(`/api/categories/${this.editingCategory.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({ name: this.editingCategory.name })
                });
                if (res.ok) {
                    this.showToast('分类更新成功', 'success');
                    this.showEditCategoryModal = false;
                    await this.loadData();
                    this.filterLinks();
                } else {
                    this.showToast('分类更新失败', 'error');
                }
            } catch (e) {
                this.showToast('分类更新失败：' + e.message, 'error');
            }
        },

        // 编辑链接相关方法
        openEditLinkModal(link) {
            this.editingLink = {
                id: link.id,
                category_id: link.category_id,
                title: link.title,
                url: link.url,
                description: link.description || '',
                need_vpn: link.need_vpn.toString(),
                icon: link.icon || '',
                sort_order: link.sort_order || 0
            };
            this.showEditLinkModal = true;
        },

        async updateLink() {
            try {
                const res = await fetch(`/api/links/${this.editingLink.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(this.editingLink)
                });

                if (res.ok) {
                    this.showToast('链接更新成功', 'success');
                    // 如果更新时图标为空，也尝试获取
                    if (!this.editingLink.icon) {
                        this.fetchIcon(this.editingLink.id);
                    }
                    this.showEditLinkModal = false;
                    this.currentTab = 'links';
                    await this.loadData();
                    this.filterLinks();
                } else {
                    this.showToast('链接更新失败', 'error');
                }
            } catch (e) {
                this.showToast('链接更新失败：' + e.message, 'error');
            }
        },

        // Toast 相关方法
        showToast(message, type = 'success') {
            // 清除之前的定时器
            if (this.toast.timer) {
                clearTimeout(this.toast.timer);
            }

            // 设置消息
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;
            this.toast.progress = 100;
            this.toast.remainingTime = 3000;

            // 使用 requestAnimationFrame 确保进度条动画平滑
            this.$nextTick(() => {
                this.animateProgress();
            });

            // 3秒后自动隐藏
            this.toast.timer = setTimeout(() => {
                this.hideToast();
            }, 3000);
        },

        animateProgress() {
            const startTime = Date.now();
            const duration = 3000;

            const updateProgress = () => {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, duration - elapsed);
                this.toast.progress = (remaining / duration) * 100;
                this.toast.remainingTime = remaining;

                if (remaining > 0 && this.toast.visible) {
                    requestAnimationFrame(updateProgress);
                }
            };

            requestAnimationFrame(updateProgress);
        },

        hideToast() {
            this.toast.visible = false;
            this.toast.progress = 0;
            if (this.toast.timer) {
                clearTimeout(this.toast.timer);
                this.toast.timer = null;
            }
        },

        // 显示自定义确认弹窗，返回 Promise<boolean>
        showConfirm(title, message, type = 'danger') {
            return new Promise(resolve => {
                this.dialog.title = title;
                this.dialog.message = message;
                this.dialog.type = type;
                this.dialog.resolve = resolve;
                this.dialog.visible = true;
            });
        },

        // 加载审计日志
        async loadAuditLogs() {
            try {
                const res = await fetch('/api/audit-logs');
                if (res.ok) {
                    this.auditLogs = await res.json();
                } else {
                    console.error('加载审计日志失败');
                }
            } catch (e) {
                console.error('加载审计日志出错:', e);
            }
        },

        // 获取筛选后的审计日志
        getFilteredAuditLogs() {
            if (!this.selectedAuditAction) {
                return this.auditLogs;
            }
            return this.auditLogs.filter(log => log.action === this.selectedAuditAction);
        }
    };
}
