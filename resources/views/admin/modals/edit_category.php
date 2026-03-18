<div x-show="showEditCategoryModal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto" style="display: none;">
    <!-- 独立的背景遮罩层 -->
    <div x-show="showEditCategoryModal" 
        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
        @click="showEditCategoryModal = false" 
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>
    
    <!-- 模态框主体内容 -->
    <div x-show="showEditCategoryModal" 
        class="relative bg-white rounded-lg p-6 w-full max-w-md my-20 shadow-xl transform transition-all"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
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