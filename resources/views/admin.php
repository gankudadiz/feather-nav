<?php $title = '管理后台'; ?>
<?= '<meta name="csrf-token" content="' . ($csrfToken ?? '') . '">' ?>

<div class="container mx-auto px-4 py-4" x-data="adminInit()">
    <!-- 引入公共 UI 组件 -->
    <?php include __DIR__ . '/admin/components/toast.php'; ?>
    <?php include __DIR__ . '/admin/components/dialog.php'; ?>

    <!-- 标签页导航 -->
    <div class="mb-4 font-medium border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="currentTab = 'statistics'; loadStatistics()"
                :class="currentTab === 'statistics' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm transition">
                📊 数据统计
            </button>
            <button @click="currentTab = 'links'"
                :class="currentTab === 'links' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm transition">
                📋 所有链接
            </button>
            <button @click="currentTab = 'addLink'"
                :class="currentTab === 'addLink' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm transition">
                ➕ 添加链接
            </button>
            <button @click="currentTab = 'categories'"
                :class="currentTab === 'categories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm transition">
                📁 分类管理
            </button>
            <button @click="currentTab = 'auditLogs'; loadAuditLogs()"
                :class="currentTab === 'auditLogs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm transition">
                🛡️ 审计日志
            </button>
        </nav>
    </div>

    <!-- 标签页内容区域 -->
    <div x-show="currentTab === 'statistics'"><?php include __DIR__ . '/admin/tabs/statistics.php'; ?></div>
    <div x-show="currentTab === 'links'"><?php include __DIR__ . '/admin/tabs/link_list.php'; ?></div>
    <div x-show="currentTab === 'addLink'"><?php include __DIR__ . '/admin/tabs/link_add.php'; ?></div>
    <div x-show="currentTab === 'categories'"><?php include __DIR__ . '/admin/tabs/category_list.php'; ?></div>
    <div x-show="currentTab === 'auditLogs'"><?php include __DIR__ . '/admin/tabs/audit_log.php'; ?></div>

    <!-- 引入模态框 -->
    <?php include __DIR__ . '/admin/modals/edit_link.php'; ?>
    <?php include __DIR__ . '/admin/modals/edit_category.php'; ?>
</div>

<!-- 引入分离出去的 JS 文件 -->
<script src="/js/admin/main.js"></script>