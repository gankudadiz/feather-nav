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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
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