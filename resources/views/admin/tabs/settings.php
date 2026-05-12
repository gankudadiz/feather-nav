<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
        <div>
            <h3 class="text-lg font-bold text-gray-800">⚙️ 系统全局设置</h3>
            <p class="text-sm text-gray-500 mt-1">管理网站的基本信息和运行策略，所有更改将实时生效。</p>
        </div>
        <button 
            @click="saveSettings()" 
            class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all flex items-center space-x-2 shadow-sm"
        >
            <span>保存所有设置</span>
        </button>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <template x-for="setting in settings" :key="setting.setting_key">
                <div class="space-y-2 p-4 rounded-xl border border-gray-100 hover:border-blue-100 hover:bg-blue-50/10 transition-all">
                    <label class="block text-sm font-semibold text-gray-700" x-text="setting.setting_name"></label>
                    <p class="text-xs text-gray-400 mb-2" x-text="'Key: ' + setting.setting_key"></p>
                    
                    <!-- 根据类型渲染不同输入框 -->
                    <template x-if="setting.setting_type === 'text'">
                        <input 
                            type="text" 
                            x-model="setting.setting_value" 
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        >
                    </template>

                    <template x-if="setting.setting_type === 'number'">
                        <input 
                            type="number" 
                            x-model="setting.setting_value" 
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        >
                    </template>

                    <template x-if="setting.setting_type === 'textarea'">
                        <textarea 
                            x-model="setting.setting_value" 
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        ></textarea>
                    </template>

                    <template x-if="setting.setting_type === 'boolean'">
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    :checked="setting.setting_value == '1'" 
                                    @change="setting.setting_value = $event.target.checked ? '1' : '0'"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-500" x-text="setting.setting_value == '1' ? '已开启' : '已关闭'"></span>
                            </label>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div x-show="settings.length === 0" class="text-center py-10">
            <div class="animate-pulse flex flex-col items-center">
                <div class="h-4 bg-gray-200 rounded w-48 mb-4"></div>
                <div class="h-4 bg-gray-200 rounded w-32"></div>
            </div>
            <p class="text-gray-400 mt-4">正在加载配置项...</p>
        </div>
    </div>
</div>

<!-- 数据库备份 -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
        <h3 class="text-lg font-bold text-gray-800">💾 数据库备份</h3>
        <p class="text-sm text-gray-500 mt-1">导出完整的数据库备份文件，包含表结构和所有数据。</p>
    </div>
    <div class="p-6">
        <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-blue-100 hover:bg-blue-50/10 transition-all">
            <div>
                <h4 class="text-sm font-semibold text-gray-700">导出 SQL 备份</h4>
                <p class="text-xs text-gray-400 mt-1">生成 .sql 文件，可直接导入到 MySQL 数据库</p>
            </div>
            <a href="/api/export/sql"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>导出备份</span>
            </a>
        </div>
    </div>
</div>
