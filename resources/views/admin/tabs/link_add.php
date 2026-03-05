<!-- 添加链接 -->
<div x-show="currentTab === 'addLink'" style="display: none;" class="mb-8">
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