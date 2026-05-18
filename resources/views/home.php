<?php // 网站标题现已由 HomeController 动态注入 ?>

<div class="container mx-auto px-4 py-8" x-data="navigation()">
    <!-- 搜索和筛选栏 -->
    <div class="mb-8 space-y-4">
        <!-- 搜索栏 -->
        <div class="flex flex-col sm:flex-row gap-4 items-center">
            <div class="flex-1 max-w-md relative">
                <!-- 诱饵字段：阻止浏览器将凭据预填到搜索框 -->
                <input type="text" name="username" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;" tabindex="-1" autocomplete="username">
                <input type="password" name="password" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;" tabindex="-1" autocomplete="current-password">
                <input
                    type="text"
                    x-model="search"
                    placeholder="搜索链接..."
                    name="q"
                    autocomplete="off"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <!-- 筛选按钮组 -->
            <div class="flex gap-2">
                <button
                    @click="filterType = 'all'"
                    :class="filterType === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    全部
                </button>
                <button
                    @click="filterType = 'no-vpn'"
                    :class="filterType === 'no-vpn' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    🛡️ 不需要翻墙
                </button>
                <button
                    @click="filterType = 'need-vpn'"
                    :class="filterType === 'need-vpn' ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    🛡️ 需要翻墙
                </button>

                <!-- 隐私空间入口 -->
                <button
                    @click="showPrivateDialog = true"
                    x-show="!privateVerified"
                    class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors"
                >
                    🔒 隐私空间
                </button>
                <span
                    x-show="privateVerified"
                    class="px-4 py-2 bg-purple-500 text-white rounded-lg text-sm font-medium"
                >
                    🔓 隐私已解锁
                </span>
            </div>
        </div>
    </div>

    <!-- 隐私密码输入弹窗 -->
    <div
        x-show="showPrivateDialog"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showPrivateDialog = false"
    >
        <div class="bg-white rounded-lg p-6 w-80 shadow-xl" @click.stop>
            <h3 class="text-lg font-bold mb-4">输入隐私空间密码</h3>
            <input
                type="password"
                x-model="privatePassword"
                @keyup.enter="verifyPrivate()"
                placeholder="请输入密码"
                class="w-full px-3 py-2 border rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
            <p x-show="privateError" class="text-red-500 text-sm mb-3" x-text="privateError"></p>
            <div class="flex justify-end gap-2">
                <button
                    @click="showPrivateDialog = false; privatePassword = ''; privateError = ''"
                    class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
                >
                    取消
                </button>
                <button
                    @click="verifyPrivate()"
                    class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors"
                >
                    确认
                </button>
            </div>
        </div>
    </div>

    <!-- 分类和链接 -->
    <template x-for="category in filteredCategories" :key="category.id">
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4" x-text="category.name"></h2>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                <template x-for="link in category.links" :key="link.id">
                    <a
                        :href="link.url"
                        target="_blank"
                        @click="recordClick(link.id)"
                        class="relative group flex flex-col items-center p-3 bg-white rounded-lg shadow hover:shadow-lg transition-all duration-200 border border-gray-100 hover:border-blue-200"
                    >
                        <!-- 翻墙标识 - 始终显示 -->
                        <div class="absolute top-1 right-1 z-10">
                            <span
                                x-show="link.need_vpn == 1"
                                class="inline-flex items-center justify-center w-4 h-4 bg-red-500 text-white text-xs rounded-full shadow-sm"
                                title="需要翻墙"
                            >
                                🛡️
                            </span>
                            <span
                                x-show="link.need_vpn == 0"
                                class="inline-flex items-center justify-center w-4 h-4 bg-green-500 text-white text-xs rounded-full shadow-sm"
                                title="不需要翻墙"
                            >
                                🛡️
                            </span>
                        </div>

                        <!-- 图标 -->
                        <img
                            :src="link.icon || '/img/logo.svg'"
                            :alt="link.title"
                            class="w-8 h-8 mb-2 group-hover:scale-110 transition-transform"
                            onerror="this.src='/img/logo.svg'"
                            referrerpolicy="no-referrer"
                        >
                        
                        <!-- 标题 -->
                        <span class="text-xs text-gray-700 text-center leading-tight line-clamp-2" x-text="link.title"></span>
                        
                        <!-- 悬停时显示描述 -->
                        <div 
                            x-show="link.description"
                            x-transition
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10"
                        >
                            <span x-text="link.description"></span>
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-800"></div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </template>

    <!-- 空状态 -->
    <div x-show="categories.length === 0" class="text-center py-20 text-gray-500">
        <p class="text-xl mb-4">还没有添加任何链接</p>
        <a href="/admin" class="text-blue-500 hover:underline">去管理页面添加</a>
    </div>
</div>

<script>
function navigation() {
    return {
        categories: [],
        search: '',
        filterType: 'all',
        // 隐私空间相关
        showPrivateDialog: false,
        privatePassword: '',
        privateError: '',
        privateVerified: false,

        async init() {
            await this.loadData();
        },

        async loadData() {
            try {
                const [categoriesRes, linksRes] = await Promise.all([
                    fetch('/api/categories'),
                    fetch('/api/links')
                ]);

                const categories = await categoriesRes.json();
                const links = await linksRes.json();

                // 检测是否包含隐私链接（表示已验证）
                this.privateVerified = links.some(link => link.is_private == 1);

                this.categories = categories.map(cat => ({
                    ...cat,
                    links: links.filter(link => link.category_id === cat.id)
                }));
            } catch (e) {
                console.error('Failed to load data:', e);
            }
        },

        async verifyPrivate() {
            this.privateError = '';
            try {
                const res = await fetch('/api/verify-private', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password: this.privatePassword })
                });
                const data = await res.json();

                if (data.success) {
                    this.showPrivateDialog = false;
                    this.privatePassword = '';
                    // 重新加载数据
                    await this.loadData();
                } else {
                    this.privateError = data.message || '密码错误';
                }
            } catch (e) {
                this.privateError = '验证失败，请重试';
            }
        },

        get filteredCategories() {
            let filteredCats = this.categories;

            // 首先按翻墙类型筛选
            if (this.filterType !== 'all') {
                filteredCats = filteredCats.map(cat => ({
                    ...cat,
                    links: cat.links.filter(link => {
                        if (this.filterType === 'need-vpn') {
                            return link.need_vpn == 1;
                        } else if (this.filterType === 'no-vpn') {
                            return link.need_vpn == 0;
                        }
                        return true;
                    })
                })).filter(cat => cat.links.length > 0);
            }

            // 然后按搜索关键词筛选
            if (this.search) {
                const keyword = this.search.toLowerCase();
                filteredCats = filteredCats.map(cat => ({
                    ...cat,
                    links: cat.links.filter(link =>
                        link.title.toLowerCase().includes(keyword) ||
                        link.url.toLowerCase().includes(keyword) ||
                        (link.description && link.description.toLowerCase().includes(keyword))
                    )
                })).filter(cat => cat.links.length > 0);
            }

            return filteredCats;
        },

        async recordClick(id) {
            try {
                // 发后不理，不阻塞跳转
                fetch(`/api/links/${id}/click`, { method: 'POST' });
            } catch (e) {
                // 静默失败
            }
        }
    };
}
</script>
