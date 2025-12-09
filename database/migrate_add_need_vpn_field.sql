-- 数据库迁移脚本：为 links 表添加 need_vpn 字段
-- 执行时间：2025-12-08

USE `personal_nav`;

-- 添加 need_vpn 字段（0-不需要翻墙，1-需要翻墙）
ALTER TABLE `links` 
ADD COLUMN `need_vpn` TINYINT(1) DEFAULT 0 COMMENT '是否需要翻墙：0-不需要，1-需要' AFTER `description`;

-- 为现有数据设置默认值（假设不需要翻墙）
UPDATE `links` SET `need_vpn` = 0 WHERE `need_vpn` IS NULL;

-- 创建索引以提高查询性能
CREATE INDEX `idx_need_vpn` ON `links` (`need_vpn`);

-- 显示更新后的表结构
DESCRIBE `links`;