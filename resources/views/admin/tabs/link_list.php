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
            <button @click="checkAllLinks()" 
                class="px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded hover:bg-indigo-100 transition flex items-center gap-2">
                <span>🔍</span>
                <span>扫码死链</span>
            </button>
        </div>

        <!-- 批量操作工具栏 -->
        <div x-show="selectedLinkIds.length > 0" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-blue-800">已选中 <span x-text="selectedLinkIds.length" class="font-bold"></span> 项</span>
                <button @click="selectedLinkIds = []" class="text-xs text-blue-600 hover:underline">取消选择</button>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1 bg-white p-1 border rounded shadow-sm">
                    <select x-model="batchTargetCategoryId" class="text-xs border-none focus:ring-0 py-1 pr-8">
                        <option value="">转移至分类...</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                        <option value="0">未分类</option>
                    </select>
                    <button @click="batchMoveLinks" 
                            :disabled="batchTargetCategoryId === ''"
                            class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 disabled:opacity-50 transition">
                        确定转移
                    </button>
                </div>
                <button @click="batchDeleteLinks" class="px-3 py-2 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition flex items-center gap-1">
                    <span>🗑️</span>
                    <span>批量删除</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 w-8">
                            <input type="checkbox" @click="toggleAllLinks" :checked="selectedLinkIds.length === paginatedLinks.length && paginatedLinks.length > 0" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700 w-12 text-center">图标</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">标题</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">分类</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">状态</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700 w-16 text-center">点击</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">URL</th>
                        <th x-show="selectedCategory !== ''" class="py-1.5 text-sm font-semibold text-gray-700 w-20 text-center">排序</th>
                        <th class="py-1.5 text-sm font-semibold text-gray-700">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="link in paginatedLinks" :key="link.id">
                        <tr class="border-b hover:bg-gray-50 transition" :class="{'bg-blue-50/50': selectedLinkIds.includes(link.id)}">
                            <td class="py-2">
                                <input type="checkbox" :value="link.id" :checked="selectedLinkIds.includes(link.id)" @click="toggleLinkSelection(link.id)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="py-1.5">
                                <div class="flex justify-center">
                                    <img :src="link.icon || '/img/logo.svg'" :alt="link.title"
                                        class="w-6 h-6 object-contain rounded shadow-sm border border-gray-100 bg-white"
                                        onerror="this.src='/img/logo.svg'" referrerpolicy="no-referrer">
                                </div>
                            </td>
                            <td class="py-1.5 text-sm" x-text="link.title"></td>
                            <td class="py-1.5 text-sm" x-text="getCategoryName(link.category_id)"></td>
                            <td class="py-1.5">
                                <!-- 组合式状态展示 -->
                                <div class="flex items-center gap-1.5">
                                    <!-- 死链检测状态 -->
                                    <template x-if="link.last_status">
                                        <span class="inline-flex items-center" :title="'最后检测状态: ' + link.last_status + ' (时间: ' + link.last_check_at + ')'">
                                            <span x-show="link.last_status >= 200 && link.last_status < 400" class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm shadow-green-200"></span>
                                            <span x-show="link.last_status >= 400" class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm shadow-red-200"></span>
                                            <span x-show="link.last_status == 0" class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                                        </span>
                                    </template>
                                    <template x-if="!link.last_status">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gray-200" title="尚未检测"></span>
                                    </template>

                                    <!-- 翻墙标识 -->
                                    <span x-show="link.need_vpn == 1" class="text-xs px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-100 font-medium">VPN</span>
                                    <span x-show="link.need_vpn == 0" class="text-xs px-1.5 py-0.5 rounded bg-green-50 text-green-600 border border-green-100 font-medium">直连</span>
                                </div>
                            </td>
                            <td class="py-1.5 text-sm text-center font-mono text-gray-500" x-text="link.click_count || 0"></td>
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
                                    <button @click="checkLink(link)"
                                        class="px-2 py-1 text-indigo-700 bg-indigo-50 rounded hover:bg-indigo-100 border border-indigo-200 transition flex items-center gap-1 text-xs"
                                        title="检测链接可用性">
                                        <span>🔍</span>
                                        <span>检测</span>
                                    </button>
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