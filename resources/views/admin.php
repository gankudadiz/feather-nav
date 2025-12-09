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
                        <button @click="deleteCategory(cat.id)" class="text-red-500 hover:text-red-700">
                            删除
                        </button>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">图标URL (可选)</label>
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
                                <button @click="deleteLink(link.id)" class="text-red-500 hover:text-red-700">
                                    删除
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function admin() {
    return {
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
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: this.newCategory })
            });
            this.newCategory = '';
            await this.loadData();
        },

        async deleteCategory(id) {
            if (!confirm('确定删除此分类？')) return;
            await fetch(`/api/categories/${id}`, { method: 'DELETE' });
            await this.loadData();
        },

        async addLink() {
            await fetch('/api/links', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.newLink)
            });
            this.newLink = { category_id: '', title: '', url: '', description: '', need_vpn: '0', icon: '' };
            await this.loadData();
        },

        async deleteLink(id) {
            if (!confirm('确定删除此链接？')) return;
            await fetch(`/api/links/${id}`, { method: 'DELETE' });
            await this.loadData();
        },

        getCategoryName(id) {
            const cat = this.categories.find(c => c.id === id);
            return cat ? cat.name : '未分类';
        }
    };
}
</script>
