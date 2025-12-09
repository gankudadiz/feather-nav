<?php $title = '我的导航'; ?>

<div class="container mx-auto px-4 py-8" x-data="navigation()">
    <!-- 搜索和筛选栏 -->
    <div class="mb-8 space-y-4">
        <!-- 搜索栏 -->
        <div class="flex flex-col sm:flex-row gap-4 items-center">
            <input
                type="text"
                x-model="search"
                placeholder="搜索链接..."
                class="flex-1 max-w-md px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            
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

                this.categories = categories.map(cat => ({
                    ...cat,
                    links: links.filter(link => link.category_id === cat.id)
                }));
            } catch (e) {
                console.error('Failed to load data:', e);
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
        }
    };
}
</script>
