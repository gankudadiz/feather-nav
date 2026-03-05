<?php $title = '管理后台'; ?>

<div class="container mx-auto px-4 py-4" x-data="admin()">
    <!-- Toast 提示组件 -->
    <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4" class="fixed top-4 right-4 z-50 max-w-sm">
        <div class="bg-white rounded-lg shadow-lg border overflow-hidden"
            :class="toast.type === 'success' ? 'border-blue-500' : 'border-red-500'">
            <div class="flex items-center p-4">
                <!-- 图标 -->
                <div class="flex-shrink-0 mr-3">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </template>
                </div>
                <!-- 消息内容 -->
                <div class="flex-1">
                    <p class="text-sm font-medium" :class="toast.type === 'success' ? 'text-blue-800' : 'text-red-800'"
                        x-text="toast.message"></p>
                </div>
                <!-- 关闭按钮 -->
                <button @click="hideToast()" class="flex-shrink-0 ml-2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <!-- 倒计时进度条 -->
            <div class="h-1 bg-gray-200" x-show="toast.visible">
                <div class="h-full transition-all ease-linear"
                    :class="toast.type === 'success' ? 'bg-blue-500' : 'bg-red-500'"
                    :style="'width: ' + toast.progress + '%; transition-duration: ' + toast.remainingTime + 'ms'"
                    x-ref="toastProgress"></div>
            </div>
        </div>
    </div>

    <!-- 自定义 Confirm 弹窗 -->
    <div x-show="dialog.visible" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0"
        style="display:none">
        <!-- 遮罩 -->
        <div x-show="dialog.visible" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-gray-600/60 transition-opacity"
            @click="dialog.visible = false; setTimeout(() => dialog.resolve(false), 200)"></div>

        <!-- 弹窗卡片 -->
        <div x-show="dialog.visible" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-white rounded-lg shadow-xl w-full max-w-sm mx-auto overflow-hidden transform transition-all">
            <div class="p-6">
                <!-- 图标 + 标题 -->
                <div class="flex items-start gap-4 mb-5">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                        :class="dialog.type === 'danger' ? 'bg-red-50' : 'bg-blue-50'">
                        <template x-if="dialog.type === 'danger'">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                        </template>
                        <template x-if="dialog.type !== 'danger'">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <div class="flex-1 pt-1">
                        <h3 class="text-base font-semibold text-gray-900" x-text="dialog.title"></h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed" x-text="dialog.message"></p>
                    </div>
                </div>
                <!-- 操作按钮 -->
                <div class="flex gap-3 justify-end mt-4">
                    <button @click="dialog.visible = false; setTimeout(() => dialog.resolve(false), 200)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors">
                        取消
                    </button>
                    <button @click="dialog.visible = false; setTimeout(() => dialog.resolve(true), 200)" :class="dialog.type === 'danger'
                                ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                                : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'"
                        class="px-4 py-2 text-sm font-medium text-white border border-transparent rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors">
                        确定
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                <input type="text" x-model="linkSearchTerm" @input.debounce.300ms="filterLinks"
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

    <!-- 添加链接 -->
    <div x-show="currentTab === 'addLink'" x-transition class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">添加链接</h2>

            <form @submit.prevent="addLink" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类</label>
                    <select x-model="newLink.category_id"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                        <option value="">选择分类</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">标题</label>
                    <input type="text" x-model="newLink.title"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="url" x-model="newLink.url"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">描述 (可选)</label>
                    <input type="text" x-model="newLink.description"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">图标URL (可选，留空自动获取)</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="newLink.icon"
                            class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="输入URL或上传图片">
                        <label
                            class="cursor-pointer px-4 py-2 bg-gray-100 border border-gray-300 text-gray-700 rounded hover:bg-gray-200 flex items-center whitespace-nowrap">
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
                            <input type="radio" x-model="newLink.need_vpn" value="0" class="mr-2">
                            <span class="text-green-600">🛡️ 不需要</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" x-model="newLink.need_vpn" value="1" class="mr-2">
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
                <input type="text" x-model="newCategory" placeholder="分类名称"
                    class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    添加
                </button>
            </form>

            <!-- 分类列表 - 添加滚动容器 -->
            <div class="max-h-96 overflow-y-auto pr-2">
                <ul class="space-y-2">
                    <template x-for="cat in categories" :key="cat.id">
                        <li
                            class="flex items-center justify-between p-1.5 bg-gray-50 rounded hover:bg-gray-100 transition">
                            <span x-text="cat.name" class="text-sm"></span>
                            <div class="flex gap-1.5">
                                <button @click="moveCategory(cat.id, -1)"
                                    class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                    title="上移">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button @click="moveCategory(cat.id, 1)"
                                    class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                    title="下移">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <button @click="openEditCategoryModal(cat)"
                                    class="px-2 py-1 text-yellow-700 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-200 transition flex items-center gap-1 text-xs">
                                    <span>✏️</span>
                                    <span>编辑</span>
                                </button>
                                <button @click="deleteCategory(cat.id)"
                                    class="px-2 py-1 text-red-700 bg-red-50 rounded hover:bg-red-100 border border-red-200 transition flex items-center gap-1 text-xs">
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
                <input type="text" x-model="editingCategory.name" placeholder="分类名称"
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                    required>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        保存
                    </button>
                    <button type="button" @click="showEditCategoryModal=false"
                        class="flex-1 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
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
                    <select x-model="editingLink.category_id"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                        <option value="">选择分类</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">标题</label>
                    <input type="text" x-model="editingLink.title"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="url" x-model="editingLink.url"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">描述 (可选)</label>
                    <input type="text" x-model="editingLink.description"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">图标URL (可选，留空自动获取)</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="editingLink.icon"
                            class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="输入URL或上传图片">
                        <label
                            class="cursor-pointer px-4 py-2 bg-gray-100 border border-gray-300 text-gray-700 rounded hover:bg-gray-200 flex items-center whitespace-nowrap">
                            <span class="mr-2">📂</span> 上传
                            <input type="file" class="hidden" @change="uploadIcon($event, editingLink)"
                                accept="image/*">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">支持上传本地图片(jpg, png, gif, ico, webp, svg)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">排序 (可选)</label>
                    <input type="number" x-model="editingLink.sort_order"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">是否需要翻墙</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" x-model="editingLink.need_vpn" value="0" class="mr-2">
                            <span class="text-green-600">🛡️ 不需要</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" x-model="editingLink.need_vpn" value="1" class="mr-2">
                            <span class="text-red-600">🛡️ 需要翻墙</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        保存
                    </button>
                    <button type="button" @click="showEditLinkModal=false"
                        class="flex-1 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
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
                }
            };
        }
    </script>
</div>