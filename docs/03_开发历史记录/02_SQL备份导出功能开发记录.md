# 02_SQL备份导出功能开发记录

## 需求背景

后台管理系统需要支持一键导出完整数据库结构和数据，便于本地存档、迁移到新服务器或灾难恢复。

## 关联提交

- `ef5a573` feat(admin): 添加数据库 SQL 备份导出功能
- `ebe12eb` feat(admin): 添加资源文件备份导出功能（同窗口补强）

## 实现要点

- Controller：`app/Controllers/ExportController.php::exportSql()`
- 路由：`GET /api/export/sql`（受 AuthMiddleware 保护）
- SQL 生成：`SHOW CREATE TABLE` 取表结构，分批（每批 1000 行）取数据并组装 `INSERT INTO ... VALUES`
- 字段转义：PDO `quote()` 处理特殊字符，表名反引号包裹
- 入口：后台「系统设置」标签页新增「导出 SQL 备份」按钮

## 测试或验证收口

- 登录校验：未登录用户被中间件拦截
- 审计日志：导出动作复用 LogHelper 记录
- 转义安全：含特殊字符的标题/URL 测试不破坏 SQL 语法
- 大库稳定：单批 1000 行降低内存峰值；GZIP 压缩留作二期

## 本阶段收益

- 数据库具备一键导出能力，迁移和备份流程大幅简化
- 复用 PDO `quote()` 避免 SQL 注入风险
- 后续若上线 GZIP 压缩，可显著降低大库导出文件体积
