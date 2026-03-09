# 链接排序功能实现计划

该计划旨在解决“链接列表缺少排序操作”的问题，通过在后台链接列表增加上移/下移功能，并实现后端批量更新接口，确保链接在首页各分类下能按预定顺序展示。

## 1. 分析与设计

### 1.1 核心逻辑
- **排序维度**：链接排序应在**分类内部**进行。在“所有分类”视图下禁用排序，避免逻辑混淆。
- **前端实现**：利用现有的 Alpine.js 状态管理，在 `filteredLinks` 数组中进行位置交换，并重新分配 `sort_order`。
- **后端实现**：增加批量更新接口，在一个数据库事务中处理多个链接的 `sort_order` 更新，保证数据一致性。

### 1.2 兼容分页
- 目前后台链接列表有分页（每页 10 条）。
- 排序操作将作用于 `filteredLinks`（即当前分类下的所有链接），操作后自动触发 `updatePaginatedLinks`，确保界面实时更新。

## 2. 实施步骤

### 第一阶段：后端接口开发
1.  **修改 `app/Controllers/LinkController.php`**：
    - 添加 `batchUpdate()` 方法：
        - 接收 JSON 格式的更新列表。
        - 校验数据合法性。
        - 开启数据库事务。
        - 循环执行 `UPDATE links SET sort_order = ? WHERE id = ?`。
2.  **修改 `app/routes.php`**：
    - 注册路由：`$router->post('/api/links/batch-update', [LinkController::class, 'batchUpdate'], ['before' => 'auth']);`（确保在 `validateCsrf` 之后或通过中间件保护）。

### 第二阶段：前端逻辑开发
1.  **修改 `public/js/admin/main.js`**：
    - 添加 `linkUpdateTimer` 变量用于防抖。
    - 实现 `moveLink(id, direction)`：
        - 仅在 `selectedCategory` 不为空时允许执行。
        - 在 `filteredLinks` 中查找 ID 并与相邻元素交换。
        - 重新计算 `filteredLinks` 中每个链接的 `sort_order`。
        - 将更新后的顺序同步回主 `links` 数组。
        - 调用 `batchUpdateLinkOrder()`。
    - 实现 `batchUpdateLinkOrder()`：
        - 收集 `filteredLinks` 的 ID 和排序值。
        - 通过 `fetch` 发送到后端接口。

### 第三阶段：UI 界面调整
1.  **修改 `resources/views/admin/tabs/link_list.php`**：
    - 在表格中增加“排序”列头。
    - 在模板行中增加排序按钮组（上移/下移）。
    - 按钮组仅在 `selectedCategory !== ''` 时显示。
    - 按钮点击时阻止默认行为并调用 `moveLink`。

## 3. 测试与验证
1.  **基础功能测试**：
    - 选中一个分类，尝试上移、下移链接。
    - 切换到首页查看顺序是否变化。
2.  **边界条件测试**：
    - 链接处于分类第一条或最后一条时的行为。
    - 跨分页排序（例如将第二页第一条移动到第一页）。
3.  **并发与性能**：
    - 连续快速点击排序按钮，确认防抖逻辑生效。
    - 检查数据库审计日志是否正确记录操作。

## 4. 为什么之前可能会失败？
- **原因 1**：尝试在“所有链接”混合视图下排序，导致跨分类的 `sort_order` 冲突。
- **原因 2**：分页逻辑导致 `paginatedLinks` 和 `filteredLinks` 脱节，排序只影响了当前页。
- **原因 3**：后端没有事务保护，批量更新中途失败导致排序数值错乱。

本计划通过“分类限定”和“全局数组操作”规避上述风险。
