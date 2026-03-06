<div x-show="currentTab === 'auditLogs'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">安全审计日志</h2>
        <div class="flex items-center gap-4">
            <!-- 动作筛选下拉框 -->
            <select x-model="selectedAuditAction" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">所有动作</option>
                <optgroup label="认证">
                    <option value="auth_login_success">登录成功</option>
                    <option value="auth_login_failed">登录失败</option>
                    <option value="auth_login_captcha">验证码错误</option>
                    <option value="auth_login_locked">账户锁定</option>
                    <option value="auth_logout">退出登录</option>
                </optgroup>
                <optgroup label="分类">
                    <option value="category_create">创建分类</option>
                    <option value="category_update">更新分类</option>
                    <option value="category_delete">删除分类</option>
                    <option value="category_batch_reorder">批量排序</option>
                </optgroup>
                <optgroup label="链接">
                    <option value="link_create">添加链接</option>
                    <option value="link_update">更新链接</option>
                    <option value="link_delete">删除链接</option>
                    <option value="link_refresh_icon">刷新图标</option>
                </optgroup>
            </select>

            <button @click="loadAuditLogs()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                刷新
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">时间</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作者</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">动作</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">描述</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP / 设备</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="log in getFilteredAuditLogs()" :key="log.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="log.created_at"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="log.username || '匿名/系统'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800" x-text="log.action"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="log.description"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                                <div x-text="log.ip_address"></div>
                                <div class="truncate max-w-xs" :title="log.user_agent" x-text="log.user_agent"></div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="getFilteredAuditLogs().length === 0">
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                没有匹配该动作的日志记录
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
