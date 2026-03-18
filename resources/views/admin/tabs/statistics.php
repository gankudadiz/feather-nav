<div class="space-y-6" x-init="loadStatistics()">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- 总链接数 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-blue-50 rounded-full">
                <span class="text-2xl">🔗</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">总链接数</p>
                <p class="text-2xl font-bold text-gray-900" x-text="stats.total_links || 0"></p>
            </div>
        </div>

        <!-- 总点击量 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-orange-50 rounded-full">
                <span class="text-2xl">🔥</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">总点击量</p>
                <p class="text-2xl font-bold text-gray-900" x-text="stats.total_clicks || 0"></p>
            </div>
        </div>

        <!-- VPN 链接数 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-purple-50 rounded-full">
                <span class="text-2xl">🌐</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">VPN 链接</p>
                <div class="flex items-baseline space-x-2">
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.vpn_links || 0"></p>
                    <p class="text-sm text-gray-500" x-text="`(${stats.vpn_percentage || 0}%)`"></p>
                </div>
            </div>
        </div>

        <!-- 死链数 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4 cursor-pointer hover:bg-red-50 transition"
             @click="currentTab = 'links'; $nextTick(() => { linkSearchTerm = ''; filterNoIcon = false; filterLinks(); })">
            <div class="p-3 bg-red-50 rounded-full">
                <span class="text-2xl">❌</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">疑似死链</p>
                <p class="text-2xl font-bold text-red-600" x-text="stats.dead_links || 0"></p>
            </div>
        </div>

        <!-- 分类总数 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-green-50 rounded-full">
                <span class="text-2xl">📁</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">分类总数</p>
                <p class="text-2xl font-bold text-gray-900" x-text="stats.total_categories || 0"></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 异常状态提示 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h3 class="text-lg font-medium text-gray-900 mb-4">异常状态</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded-md bg-yellow-50 border border-yellow-100">
                    <div class="flex items-center space-x-3">
                        <span class="text-yellow-600">⚠️</span>
                        <span class="text-sm font-medium text-yellow-800">未分类链接</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                        x-text="stats.uncategorized_links || 0"></span>
                </div>

                <div @click="currentTab = 'links'; $nextTick(() => { linkSearchTerm = ''; filterNoIcon = true; filterLinks(); })"
                    class="flex items-center justify-between p-3 rounded-md bg-red-50 border border-red-100 cursor-pointer hover:bg-red-100 transition">
                    <div class="flex items-center space-x-3">
                        <span class="text-red-600">🖼️</span>
                        <span class="text-sm font-medium text-red-800">无图标链接</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                            x-text="stats.no_icon_links || 0"></span>
                        <span class="text-xs text-red-600">点击查看</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 快捷操作与导出 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h3 class="text-lg font-medium text-gray-900 mb-4">操作与管理</h3>
            <div class="grid grid-cols-2 gap-3">
                <button @click="currentTab = 'addLink'"
                    class="flex items-center justify-center space-x-2 p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition text-sm">
                    <span>➕</span>
                    <span>添加链接</span>
                </button>
                <button @click="currentTab = 'categories'"
                    class="flex items-center justify-center space-x-2 p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition text-sm">
                    <span>📁</span>
                    <span>管理分类</span>
                </button>
                <button @click="exportData('json')"
                    class="flex items-center justify-center space-x-2 p-3 rounded-md border border-blue-200 hover:bg-blue-50 transition text-sm text-blue-700">
                    <span>📦</span>
                    <span>导出 JSON</span>
                </button>
                <button @click="exportData('html')"
                    class="flex items-center justify-center space-x-2 p-3 rounded-md border border-purple-200 hover:bg-purple-50 transition text-sm text-purple-700">
                    <span>🔖</span>
                    <span>导出书签</span>
                </button>
                <button @click="loadStatistics()"
                    class="flex items-center justify-center space-x-2 p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition text-sm col-span-2">
                    <span>🔄</span>
                    <span>刷新统计数据</span>
                </button>
            </div>
        </div>

        <!-- 数据导入 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 md:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">数据导入 <span
                    class="text-sm text-gray-500 font-normal ml-2">支持 JSON 及浏览器 HTML 书签</span></h3>
            <div class="flex flex-col lg:flex-row items-start lg:items-center space-y-4 lg:space-y-0 lg:space-x-4">
                <input type="file" x-ref="importFile" accept=".json,.html"
                    class="block w-full lg:flex-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-md">

                <select x-model="importStrategy"
                    class="w-full lg:w-48 border border-gray-200 rounded-md focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 text-sm bg-white hover:bg-gray-50 transition">
                    <option value="skip">碰到相同网址：跳过</option>
                    <option value="update">碰到相同网址：覆盖</option>
                </select>

                <button @click="parseImportFile()" :disabled="isParsing"
                    class="w-full lg:w-auto whitespace-nowrap flex items-center justify-center space-x-2 py-2 px-6 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition font-medium disabled:opacity-50">
                    <span x-show="isParsing" class="inline-block animate-spin" style="display: none;">⏳</span>
                    <span x-text="isParsing ? '解析中...' : '解析并预览'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- 导入预览模态框 -->
    <div x-show="showImportPreviewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- 背景遮罩 -->
            <div x-show="showImportPreviewModal" @click="showImportPreviewModal = false"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- 模态框主体 -->
            <div x-show="showImportPreviewModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                导入数据预览 <span class="text-sm font-normal text-gray-500 ml-2">请勾选需要导入的内容 (检测到 <span
                                        x-text="importTotalLinks"></span> 条)</span>
                            </h3>

                            <div class="mb-4">
                                <input type="text" x-model="importSearchTerm" placeholder="搜索书签标题或网址..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            </div>

                            <div class="max-h-[50vh] overflow-y-auto border border-gray-200 rounded-md bg-gray-50 p-2">
                                <template x-for="category in filteredImportPreview" :key="category.category_name">
                                    <div class="mb-4 border-b border-gray-200 pb-2 last:border-b-0 last:pb-0">
                                        <div class="flex items-center font-medium bg-gray-100 p-2 rounded-t-md cursor-pointer hover:bg-gray-200"
                                            @click="category._expanded = !category._expanded">
                                            <div class="flex-shrink-0 flex items-center h-4 mr-2" @click.stop>
                                                <input type="checkbox" x-model="category._allSelected"
                                                    @change="category.links.forEach(l => l._selected = category._allSelected)"
                                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                            </div>
                                            <span class="text-gray-500 mr-2 transition-transform duration-200"
                                                :class="{'rotate-90': category._expanded}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </span>
                                            <span class="text-gray-700" x-text="category.category_name"></span>
                                            <span class="ml-2 text-xs text-gray-500">(<span
                                                    x-text="category.links.length"></span> 条 )</span>
                                        </div>
                                        <div x-show="category._expanded" class="pl-6 pt-2 space-y-2 pb-2">
                                            <template x-for="link in category.links" :key="link._temp_id">
                                                <label
                                                    class="flex items-start space-x-3 text-sm cursor-pointer p-1 hover:bg-white rounded">
                                                    <input type="checkbox" x-model="link._selected"
                                                        @change="checkImportCategoryState(category)"
                                                        class="mt-1 flex-shrink-0 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                    <div class="flex-1 overflow-hidden">
                                                        <div class="font-medium text-gray-700 truncate"><span
                                                                x-show="link.need_vpn"
                                                                class="text-purple-500 mr-1">🌐</span><span
                                                                x-text="link.title"></span></div>
                                                        <div class="text-xs text-gray-400 truncate mt-0.5"
                                                            x-text="link.url"></div>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredImportPreview.length === 0"
                                    class="text-center py-8 text-gray-500 text-sm">
                                    没有找到匹配的书签记录 😅
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="button" @click="confirmImport()" :disabled="isImporting"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="isImporting" class="inline-block animate-spin mr-2"
                            style="display: none;">⏳</span>
                        确认导入已选 ( <span x-text="selectedImportCount"></span> 条 )
                    </button>
                    <button type="button" @click="showImportPreviewModal = false" :disabled="isImporting"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        取消
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>