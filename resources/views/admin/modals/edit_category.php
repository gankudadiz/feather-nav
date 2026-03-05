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