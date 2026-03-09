<!-- 分类管理 -->
<div x-show="currentTab === 'categories'" style="display: none;" class="mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">分类管理</h2>

        <!-- 添加分类 -->
        <form @submit.prevent="addCategory" class="flex gap-2 mb-4">
            <input type="text" x-model="newCategory" placeholder="分类名称"
                class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                添加
            </button>
        </form>

        <!-- 分类列表 - 添加滚动容器 -->
        <div class="max-h-96 overflow-y-auto pr-2">
            <ul class="space-y-2">
                <template x-for="cat in categories" :key="cat.id">
                    <li class="flex items-center justify-between p-1.5 bg-gray-50 rounded hover:bg-gray-100 transition">
                        <div class="flex items-center gap-2">
                            <span x-text="cat.name" class="text-sm font-medium"></span>
                            <span x-show="cat.link_count > 0" x-text="cat.link_count" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                title="包含链接数">
                            </span>
                            <span x-show="cat.link_count == 0" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-500"
                                title="无链接">
                                0
                            </span>
                        </div>
                        <div class="flex gap-1.5">
                            <button @click="moveCategory(cat.id, -1)"
                                class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                title="上移">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button @click="moveCategory(cat.id, 1)"
                                class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 border border-blue-200 transition"
                                title="下移">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
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