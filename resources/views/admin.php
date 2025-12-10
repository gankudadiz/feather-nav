<?php $title = '管理后台'; ?>

<div class="container mx-auto px-4 py-4" x-data="admin()">
    <!-- 标签页导航 - 紧凑布局 -->
    <div class="mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="currentTab = 'links'"
                        :class="currentTab === 'links' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                    📋 所有链接
                </button>
                <button @click="currentTab = 'addLink'"
                        :class="currentTab === 'addLink' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                    ➕ 添加链接
                </button>
                <button @click="currentTab = 'categories'"
                        :class="currentTab === 'categories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                    📁 分类管理
                </button>
            </nav>
        </div>
    </div>

    <!-- 标签页内容 -->
    <!-- 所有链接 -->
    <div x-show="currentTab === 'links'" x-transition class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">所有链接</h2>

            <!-- 添加搜索栏 -->
            <div class="mb-4 flex flex-col md:flex-row gap-3">
                <input type="text"
                       x-model="linkSearchTerm"
                       @input.debounce.300ms="filterLinks"
                       placeholder="搜索标题/URL/描述..."
                       class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select x-model="selectedCategory" @change="filterLinks"
                        class="px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">所有分类</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-1.5 text-sm font-semibold text-gray-700">标题</th>
                            <th class="py-1.5 text-sm font-semibold text-gray-700">分类</th>
                            <th class="py-1.5 text-sm font-semibold text-gray-700">翻墙</th>
                            <th class="py-1.5 text-sm font-semibold text-gray-700">URL</th>
                            <th class="py-1.5 text-sm font-semibold text-gray-700">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="link in paginatedLinks" :key="link.id">
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-1.5 text-sm" x-text="link.title"></td>
                                <td class="py-1.5 text-sm" x-text="getCategoryName(link.category_id)"></td>
                                <td class="py-1.5">
                                    <span
                                        x-show="link.need_vpn == 1"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                    >
                                        🛡️ 需要翻墙
                                    </span>
                                    <span
                                        x-show="link.need_vpn == 0"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                    >
                                        🛡️ 不需要
                                    </span>
                                </td>
                                <td class="py-1.5">
                                    <a :href="link.url" target="_blank" class="text-blue-500 hover:underline truncate block max-w-xs text-sm" x-text="link.url"></a>
                                </td>
                                <td class="py-1.5">
                                    <div class="flex gap-1.5">
                                        <button @click="openEditLinkModal(link)" class="px-2 py-1 text-yellow-700 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-200 transition flex items-center gap-1 text-xs">
                                            <span>✏️</span>
                                            <span>编辑</span>
                                        </button>
                                        <button @click="deleteLink(link.id)" class="px-2 py-1 text-red-700 bg-red-50 rounded hover:bg-red-100 border border-red-200 transition flex items-center gap-1 text-xs">
                                            <span>🗑️</span>
                                            <span>删除</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- 分页控件 -->
            <div x-show="totalPages > 1" class="flex items-center justify-between mt-4 pt-4 border-t">
                <div class="text-sm text-gray-600">
                    第 <span x-text="currentPage"></span> / <span x-text="totalPages"></span> 页，共 <span x-text="filteredLinks.length"></span> 条
                </div>
                <div class="flex gap-2">
                    <button @click="changePage(1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">首页</button>
                    <button @click="changePage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">上一页</button>
                    <button @click="changePage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">下一页</button>
                    <button @click="changePage(totalPages)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">末页</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加链接 -->
    <div x-show="currentTab === 'addLink'" x-transition class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">添加链接</h2>

            <form @submit.prevent="addLink" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类</label>
                    <select
                        x-model="newLink.category_id"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                        <option value="">选择分类</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">标题</label>
                    <input
                        type="text"
                        x-model="newLink.title"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input
                        type="url"
                        x-model="newLink.url"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">描述 (可选)</label>
                    <input
                        type="text"
                        x-model="newLink.description"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">图标URL (可选，留空自动获取)</label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            x-model="newLink.icon"
                            class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="输入URL或上传图片"
                        >
                        <label class="cursor-pointer px-4 py-2 bg-gray-100 border border-gray-300 text-gray-700 rounded hover:bg-gray-200 flex items-center whitespace-nowrap">
                            <span class="mr-2">📂</span> 上传
                            <input type="file" class="hidden" @change="uploadIcon($event, newLink)" accept="image/*">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">支持上传本地图片(jpg, png, gif, ico, webp, svg)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">是否需要翻墙</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                x-model="newLink.need_vpn"
                                value="0"
                                class="mr-2"
                            >
                            <span class="text-green-600">🛡️ 不需要</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                x-model="newLink.need_vpn"
                                value="1"
                                class="mr-2"
                            >
                            <span class="text-red-600">🛡️ 需要翻墙</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    添加链接
                </button>
            </form>
        </div>
    </div>

    <!-- 分类管理 -->
    <div x-show="currentTab === 'categories'" x-transition class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">分类管理</h2>

            <!-- 添加分类 -->
            <form @submit.prevent="addCategory" class="flex gap-2 mb-4">
                <input
                    type="text"
                    x-model="newCategory"
                    placeholder="分类名称"
                    class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    添加
                </button>
            </form>

            <!-- 分类列表 - 添加滚动容器 -->
            <div class="max-h-96 overflow-y-auto pr-2">
                <ul class="space-y-2">
                    <template x-for="cat in categories" :key="cat.id">
                        <li class="flex items-center justify-between p-1.5 bg-gray-50 rounded hover:bg-gray-100 transition">
                            <span x-text="cat.name" class="text-sm"></span>
                            <div class="flex gap-1.5">
                                <button @click="moveCategory(cat.id, -1)"
                                        class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                        title="上移">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button @click="moveCategory(cat.id, 1)"
                                        class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                        title="下移">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <button @click="openEditCategoryModal(cat)" class="px-2 py-1 text-yellow-700 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-200 transition flex items-center gap-1 text-xs">
                                    <span>✏️</span>
                                    <span>编辑</span>
                                </button>
                                <button @click="deleteCategory(cat.id)" class="px-2 py-1 text-red-700 bg-red-50 rounded hover:bg-red-100 border border-red-200 transition flex items-center gap-1 text-xs">
                                    <span>🗑️</span>
                                    <span>删除</span>
                                </button>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <!-- 分类编辑模态框 -->
    <div x-show="showEditCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50" x-transition>
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto mt-20" @click.away="showEditCategoryModal=false">
            <h3 class="text-lg font-bold mb-4">编辑分类</h3>
            <form @submit.prevent="updateCategory">
                <input
                    type="text"
                    x-model="editingCategory.name"
                    placeholder="分类名称"
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                    required
                >
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        保存
                    </button>
                    <button type="button" @click="showEditCategoryModal=false" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        取消
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 链接编辑模态框 -->
    <div x-show="showEditLinkModal" class="fixed inset-0 bg-black bg-opacity-50 z-50" x-transition>
        <div class="bg-white rounded-lg p-6 max-w-2xl mx-auto mt-10" @click.away="showEditLinkModal=false">
            <h3 class="text-lg font-bold mb-4">编辑链接</h3>
            <form @submit.prevent="updateLink" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类</label>
                    <select
                        x-model="editingLink.category_id"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                        <option value="">选择分类</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">标题</label>
                    <input
                        type="text"
                        x-model="editingLink.title"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input
                        type="url"
                        x-model="editingLink.url"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">描述 (可选)</label>
                    <input
                        type="text"
                        x-model="editingLink.description"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">图标URL (可选，留空自动获取)</label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            x-model="editingLink.icon"
                            class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="输入URL或上传图片"
                        >
                        <label class="cursor-pointer px-4 py-2 bg-gray-100 border border-gray-300 text-gray-700 rounded hover:bg-gray-200 flex items-center whitespace-nowrap">
                            <span class="mr-2">📂</span> 上传
                            <input type="file" class="hidden" @change="uploadIcon($event, editingLink)" accept="image/*">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">支持上传本地图片(jpg, png, gif, ico, webp, svg)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">排序 (可选)</label>
                    <input
                        type="number"
                        x-model="editingLink.sort_order"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">是否需要翻墙</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                x-model="editingLink.need_vpn"
                                value="0"
                                class="mr-2"
                            >
                            <span class="text-green-600">🛡️ 不需要</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                x-model="editingLink.need_vpn"
                                value="1"
                                class="mr-2"
                            >
                            <span class="text-red-600">🛡️ 需要翻墙</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        保存
                    </button>
                    <button type="button" @click="showEditLinkModal=false" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        取消
                    </button>
                </div>
            </form>
        </div>
    </div>


<script>
function admin() {
    return {
        csrfToken: '<?= $csrfToken ?? "" ?>',
        categories: [],
        links: [],
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
        currentTab: 'links', // 默认显示"所有链接"标签

        // 编辑相关数据
        showEditCategoryModal: false,
        showEditLinkModal: false,
        editingCategory: { id: null, name: '' },
        editingLink: { id: null, category_id: '', title: '', url: '', description: '', need_vpn: '0', icon: '', sort_order: 0 },

        // 新增：链接搜索和分页
        filteredLinks: [],
        paginatedLinks: [],
        linkSearchTerm: '',
        selectedCategory: '',
        currentPage: 1,
        perPage: 10,
        totalPages: 0,

        // 新增：分类排序
        categoryUpdateTimer: null,

        async init() {
            await this.loadData();
            this.filterLinks(); // 初始化时应用筛选
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

        async addCategory() {
            await fetch('/api/categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({ name: this.newCategory })
            });
            this.newCategory = '';
            await this.loadData();
            this.filterLinks();
        },

        // 新增：筛选链接
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

            this.filteredLinks = filtered;
            this.currentPage = 1; // 重置到第一页
            this.updatePaginatedLinks();
        },

        // 新增：更新分页数据
        updatePaginatedLinks() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            this.paginatedLinks = this.filteredLinks.slice(start, end);
            this.totalPages = Math.ceil(this.filteredLinks.length / this.perPage);
        },

        // 新增：切换页面
        changePage(page) {
            if (page < 1 || page > Math.ceil(this.filteredLinks.length / this.perPage)) return;
            this.currentPage = page;
            this.updatePaginatedLinks();
        },

        // 新增：移动分类
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

        // 新增：批量更新分类排序
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
            if (!confirm('确定删除此分类？')) return;
            await fetch(`/api/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': this.csrfToken
                }
            });
            await this.loadData();
            this.filterLinks();
        },

        async addLink() {
            const res = await fetch('/api/links', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify(this.newLink)
            });

            if (!res.ok) return;
            const data = await res.json();

            // 异步获取图标
            if (!this.newLink.icon) {
                this.fetchIcon(data.id);
            }

            this.newLink = { category_id: '', title: '', url: '', description: '', need_vpn: '0', icon: '' };
            await this.loadData();
            this.filterLinks();
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
            if (!confirm('确定删除此链接？')) return;
            await fetch(`/api/links/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': this.csrfToken
                }
            });
            await this.loadData();
            this.filterLinks();
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
                    alert(data.error || '上传失败');
                    return;
                }

                targetLink.icon = data.url;
                event.target.value = ''; // Reset file input
            } catch (e) {
                console.error('Upload failed:', e);
                alert('上传出错，请检查网络或重试');
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
            await fetch(`/api/categories/${this.editingCategory.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({ name: this.editingCategory.name })
            });
            this.showEditCategoryModal = false;
            await this.loadData();
            this.filterLinks();
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
            await fetch(`/api/links/${this.editingLink.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify(this.editingLink)
            });

            // 如果更新时图标为空，也尝试获取
            if (!this.editingLink.icon) {
                this.fetchIcon(this.editingLink.id);
            }

            this.showEditLinkModal = false;
            await this.loadData();
            this.filterLinks();
        }
    };
}
</script>
</div>
