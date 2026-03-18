<div x-show="showEditLinkModal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto" style="display: none;">
    <!-- 独立的背景遮罩层 -->
    <div x-show="showEditLinkModal" 
        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
        @click="showEditLinkModal = false" 
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>
    
    <!-- 模态框主体内容 -->
    <div x-show="showEditLinkModal" 
        class="relative bg-white rounded-lg p-6 w-full max-w-2xl my-10 shadow-xl transform transition-all"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
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
                        <input type="file" class="hidden" @change="uploadIcon($event, editingLink)" accept="image/*">
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