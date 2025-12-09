<?php $title = '管理后台'; ?>

<div class="container mx-auto px-4 py-8" x-data="admin()">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">管理后台</h1>
        <a href="/" class="text-blue-500 hover:underline">← 返回首页</a>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- 分类管理 -->
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

            <!-- 分类列表 -->
            <ul class="space-y-2">
                <template x-for="cat in categories" :key="cat.id">
                    <li class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span x-text="cat.name"></span>
                        <div class="flex gap-2">
                            <button @click="openEditCategoryModal(cat)" class="text-yellow-600 hover:text-yellow-800">
                                ✏️ 编辑
                            </button>
                            <button @click="deleteCategory(cat.id)" class="text-red-500 hover:text-red-700">
                                删除
                            </button>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <!-- 链接管理 -->
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
                    <input
                        type="url"
                        x-model="newLink.icon"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
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

                <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    添加链接
                </button>
            </form>
        </div>
    </div>

    <!-- 链接列表 -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">所有链接</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">标题</th>
                        <th class="py-2">分类</th>
                        <th class="py-2">翻墙</th>
                        <th class="py-2">URL</th>
                        <th class="py-2">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="link in links" :key="link.id">
                        <tr class="border-b">
                            <td class="py-2" x-text="link.title"></td>
                            <td class="py-2" x-text="getCategoryName(link.category_id)"></td>
                            <td class="py-2">
                                <span 
                                    x-show="link.need_vpn == 1"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                >
                                    🛡️ 需要翻墙
                                </span>
                                <span 
                                    x-show="link.need_vpn == 0"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    🛡️ 不需要
                                </span>
                            </td>
                            <td class="py-2">
                                <a :href="link.url" target="_blank" class="text-blue-500 hover:underline truncate block max-w-xs" x-text="link.url"></a>
                            </td>
                            <td class="py-2">
                                <div class="flex gap-2">
                                    <button @click="openEditLinkModal(link)" class="text-yellow-600 hover:text-yellow-800">
                                        ✏️ 编辑
                                    </button>
                                    <button @click="deleteLink(link.id)" class="text-red-500 hover:text-red-700">
                                        删除
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
                    <input
                        type="url"
                        x-model="editingLink.icon"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
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

        // 编辑相关数据
        showEditCategoryModal: false,
        showEditLinkModal: false,
        editingCategory: { id: null, name: '' },
        editingLink: { id: null, category_id: '', title: '', url: '', description: '', need_vpn: '0', icon: '', sort_order: 0 },

        async init() {
            await this.loadData();
        },

        async loadData() {
            const [categoriesRes, linksRes] = await Promise.all([
                fetch('/api/categories'),
                fetch('/api/links')
            ]);
            this.categories = await categoriesRes.json();
            this.links = await linksRes.json();
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
        }
    };
}
</script>
