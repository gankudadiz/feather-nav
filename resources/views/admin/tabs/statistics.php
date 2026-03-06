<div class="space-y-6" x-init="loadStatistics()">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" x-text="stats.uncategorized_links || 0"></span>
                </div>
                
                <div @click="currentTab = 'links'; $nextTick(() => { linkSearchTerm = ''; filterNoIcon = true; filterLinks(); })" 
                     class="flex items-center justify-between p-3 rounded-md bg-red-50 border border-red-100 cursor-pointer hover:bg-red-100 transition">
                    <div class="flex items-center space-x-3">
                        <span class="text-red-600">🖼️</span>
                        <span class="text-sm font-medium text-red-800">无图标链接</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" x-text="stats.no_icon_links || 0"></span>
                        <span class="text-xs text-red-600">点击查看</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 快捷操作 -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h3 class="text-lg font-medium text-gray-900 mb-4">快捷操作</h3>
            <div class="grid grid-cols-2 gap-3">
                <button @click="currentTab = 'addLink'" class="flex items-center justify-center space-x-2 p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition text-sm">
                    <span>➕</span>
                    <span>添加链接</span>
                </button>
                <button @click="currentTab = 'categories'" class="flex items-center justify-center space-x-2 p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition text-sm">
                    <span>📁</span>
                    <span>管理分类</span>
                </button>
                <button @click="loadStatistics()" class="flex items-center justify-center space-x-2 p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition text-sm col-span-2">
                    <span>🔄</span>
                    <span>刷新统计数据</span>
                </button>
            </div>
        </div>
    </div>
</div>
