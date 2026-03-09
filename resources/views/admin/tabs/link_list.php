<!-- 所有链接 -->
<div x-show="currentTab === 'links'" class="mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">所有链接</h2>

        <!-- 添加搜索栏 -->
        <div class="mb-4 flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" x-model="linkSearchTerm" @input.debounce.300ms="filterLinks" placeholder="搜索标题/URL/描述..."
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div x-show="filterNoIcon" class="absolute right-3 top-2 flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        无图标
                    </span>
                    <button @click="filterNoIcon = false; filterLinks()" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>
            </div>
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
                        <th class="py-1.5 text-sm font-semibold text-gray-700 w-12">图标</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">标题</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">分类</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">翻墙</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">URL</th>
                        <th x-show="selectedCategory !== ''" class="py-1.5 text-sm font-semibold text-gray-700 w-20 text-center">排序</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="link in paginatedLinks" :key="link.id">
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-1.5">
                                <img :src="link.icon || '/img/logo.svg'" :alt="link.title"
                                    class="w-6 h-6 object-contain rounded shadow-sm border border-gray-100 bg-white"
                                    onerror="this.src='/img/logo.svg'" referrerpolicy="no-referrer">
                            </td>
                            <td class="py-1.5 text-sm" x-text="link.title"></td>
                            <td class="py-1.5 text-sm" x-text="getCategoryName(link.category_id)"></td>
                            <td class="py-1.5">
                                <span x-show="link.need_vpn == 1"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    🛡️ 需要翻墙
                                </span>
                                <span x-show="link.need_vpn == 0"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    🛡️ 不需要
                                </span>
                            </td>
                            <td class="py-1.5">
                                <a :href="link.url" target="_blank"
                                    class="text-blue-500 hover:underline truncate block max-w-xs text-sm"
                                    x-text="link.url"></a>
                            </td>
                            <td x-show="selectedCategory !== ''" class="py-1.5 text-center">
                                <div class="flex justify-center gap-1">
                                    <button @click="moveLink(link.id, -1)"
                                        class="p-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                        title="上移">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button @click="moveLink(link.id, 1)"
                                        class="p-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                        title="下移">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="py-1.5">
                                <div class="flex gap-1.5">
                                    <button @click="openEditLinkModal(link)"
                                        class="px-2 py-1 text-yellow-700 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-200 transition flex items-center gap-1 text-xs">
                                        <span>✏️</span>
                                        <span>编辑</span>
                                    </button>
                                    <button @click="deleteLink(link.id)"
                                        class="px-2 py-1 text-red-700 bg-red-50 rounded hover:bg-red-100 border border-red-200 transition flex items-center gap-1 text-xs">
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
                第 <span x-text="currentPage"></span> / <span x-text="totalPages"></span> 页，共 <span
                    x-text="filteredLinks.length"></span> 条
            </div>
            <div class="flex gap-2">
                <button @click="changePage(1)" :disabled="currentPage === 1"
                    class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">首页</button>
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                    class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">上一页</button>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                    class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">下一页</button>
                <button @click="changePage(totalPages)" :disabled="currentPage === totalPages"
                    class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-50 text-sm">末页</button>
            </div>
        </div>
    </div>
</div>