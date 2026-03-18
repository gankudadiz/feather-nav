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
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed whitespace-pre-line" x-text="dialog.message"></p>
                    
                    <!-- 动态注入的 VPN 选项 (仅在全量检测时触发) -->
                    <template x-if="dialog.showVpnToggle">
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <label class="flex items-center cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" x-model="dialog.vpnEnabled" class="sr-only">
                                    <div class="w-10 h-5 bg-gray-200 rounded-full shadow-inner transition-colors duration-300" :class="dialog.vpnEnabled ? 'bg-blue-500' : 'bg-gray-200'"></div>
                                    <div class="absolute inset-y-0 left-0 w-5 h-5 bg-white rounded-full shadow transform transition-transform duration-300" :class="dialog.vpnEnabled ? 'translate-x-5' : 'translate-x-0'"></div>
                                </div>
                                <div class="ml-3 text-sm font-medium text-gray-700 select-none">
                                    同时探测需 VPN 的链接
                                    <p class="text-[10px] text-gray-400 font-normal mt-0.5">※ 若服务器环境未开启 VPN，此类链接可能被误报为失效</p>
                                </div>
                            </label>
                        </div>
                    </template>
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