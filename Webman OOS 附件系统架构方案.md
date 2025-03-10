# Webman OOS 附件系统架构方案

## 1. 概述
OOS (Object Oriented Storage) 是基于 webman 框架开发的高性能附件管理系统，专注于图片文件的本地存储与管理。本系统采用 PHP 8.x + webman 框架构建，具备高性能、易扩展、低耦合的特点，为应用提供统一的附件管理服务。

## 3. 系统组件详解

### 3.1 接入层

- 3.1.1 Nginx/Apache 反向代理

	- 负责请求的接收与转发

	- 实现负载均衡

	- 静态资源访问加速

	- 图片防盗链配置

- 3.1.2 Webman 服务器

	- 基于 workerman 的高性能 PHP 框架

	- 常驻内存，避免重复加载

	- 协程支持，提升并发性能

	- 中间件机制，实现请求拦截与处理

### 3.2 应用层

- 3.2.1 上传管理模块

	- 文件上传接收与验证

	- 分片上传支持

	- 文件类型限制

	- 文件大小控制

	- 上传进度跟踪

- 3.2.2 下载管理模块

	- 文件下载控制

	- 访问权限验证

	- 下载速率限制

	- 断点续传支持

	- 访问统计与记录

- 3.2.3 元数据管理

	- 文件信息管理

	- 目录结构管理

	- 标签与分类管理

	- 关联关系管理

- 3.2.4 权限控制

	- 用户权限管理

	- 文件访问控制

	- 角色与权限分配

	- 授权码机制

- 3.2.5 审计日志

	- 操作日志记录

	- 安全审计

	- 异常行为监控

	- 日志分析与导出

- 3.2.6 缩略图生成模块

	- 自动生成不同尺寸缩略图

	- 缩略图缓存管理

	- 按需生成机制

	- 图片质量控制

- 3.2.7 水印处理模块

	- 图片水印添加

	- 文字水印支持

	- 水印位置与透明度调整

	- 批量水印处理

- 3.2.8 格式转换模块

	- 图片格式转换

	- WebP 格式支持

	- 图片压缩优化

	- 图片品质控制

- 3.2.9 文件清理模块

	- 孤立文件检测

	- 过期文件自动清理

	- 存储空间回收

	- 垃圾数据处理

- 3.2.10 定时任务

	- 系统状态检测

	- 存储空间监控

	- 定期数据备份

	- 缓存更新与维护

### 3.3 服务层

- 3.3.1 文件存储服务

	- **本地存储策略**

		- 多级目录结构设计

		- 基于日期、哈希等的目录分配

		- 同名文件处理策略

	- **目录结构管理**

		- 自动创建目录

		- 目录容量控制

		- 目录平衡策略

	- **分片存储**

		- 大文件分片管理

		- 断点续传支持

		- 分片合并与验证

- 3.3.2 文件索引服务

	- **文件元数据索引**

		- 文件名、大小、类型等基础信息

		- 上传时间、上传用户等扩展信息

		- 自定义元数据支持

	- **高效检索算法**

		- 数据库索引优化

		- 全文检索支持

		- 复合查询条件

	- **关联关系管理**

		- 文件与业务数据关联

		- 文件分组管理

		- 文件标签系统

- 3.3.3 统计分析服务

	- **存储空间统计**

		- 总体空间使用情况

		- 各类型文件占比

		- 存储趋势分析

	- **访问热点分析**

		- 文件访问频率统计

		- 流量使用分析

		- 热点文件识别

	- **性能监控**

		- 系统响应时间监控

		- 资源占用情况

		- 异常情况报警

### 3.4 数据层

- 3.4.1 物理存储

	- **本地文件系统**

		- 直接利用服务器本地磁盘

		- 可扩展至 NAS/SAN 存储

		- 支持多磁盘分区

	- **目录分级存储**

		- 基于哈希的目录划分

		- 按日期/月份自动分目录

		- 防止单目录文件过多

	- **文件命名规则**

		- 基于 UUID/MD5 等的唯一命名

		- 时间戳前缀

		- 原始文件名保存

- 3.4.2 数据库

	- **MySQL/MariaDB**

		- 高性能关系型数据库

		- 事务支持

		- 索引优化

	- **元数据表**``` sql
CREATE TABLE `oos_files` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_hash` char(32) NOT NULL COMMENT '文件MD5哈希',
  `original_name` varchar(255) NOT NULL COMMENT '原始文件名',
  `storage_name` varchar(255) NOT NULL COMMENT '存储文件名',
  `storage_path` varchar(255) NOT NULL COMMENT '存储路径',
  `file_ext` varchar(10) NOT NULL COMMENT '文件扩展名',
  `file_size` bigint(20) UNSIGNED NOT NULL COMMENT '文件大小(字节)',
  `file_type` varchar(100) NOT NULL COMMENT '文件MIME类型',
  `image_width` int(11) DEFAULT NULL COMMENT '图片宽度',
  `image_height` int(11) DEFAULT NULL COMMENT '图片高度',
  `upload_time` datetime NOT NULL COMMENT '上传时间',
  `upload_user_id` int(11) DEFAULT NULL COMMENT '上传用户ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1正常,0已删除',
  `access_count` int(11) NOT NULL DEFAULT '0' COMMENT '访问次数',
  `last_access_time` datetime DEFAULT NULL COMMENT '最后访问时间',
  `extra` json DEFAULT NULL COMMENT '额外信息',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_file_hash` (`file_hash`),
  KEY `idx_upload_time` (`upload_time`),
  KEY `idx_upload_user` (`upload_user_id`),
  KEY `idx_storage_path` (`storage_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件存储表';
```


	- **关联关系表**``` sql
CREATE TABLE `oos_file_relations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` bigint(20) UNSIGNED NOT NULL COMMENT '文件ID',
  `business_type` varchar(50) NOT NULL COMMENT '业务类型',
  `business_id` varchar(64) NOT NULL COMMENT '业务ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `extra` json DEFAULT NULL COMMENT '额外信息',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_business` (`business_type`,`business_id`,`file_id`),
  KEY `idx_file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件关联表';
```


- 3.4.3 缓存系统

	- **Redis**

		- 高性能键值存储

		- 过期时间设置

		- 分布式锁支持

	- **热点文件缓存**

		- 热门图片内存缓存

		- LRU 淘汰策略

		- 预加载机制

	- **元数据缓存**

		- 文件信息快速查询

		- 关系数据缓存

		- 统计数据缓存

## 4. 核心流程

### 4.1 文件上传流程

- 1. 客户端发起上传请求

- 2. 接入层接收请求并进行基本验证

- 3. 应用层处理上传请求:

	- 验证文件类型、大小

	- 计算文件哈希

	- 检查文件是否已存在(秒传)

- 4. 服务层存储文件:

	- 确定存储路径

	- 生成存储文件名

	- 写入物理文件系统

- 5. 服务层记录元数据:

	- 存储文件信息到数据库

	- 建立业务关联关系

- 6. 应用层处理后处理:

	- 生成缩略图(如需)

	- 添加水印(如需)

- 7. 返回上传结果给客户端

### 4.2 文件访问流程

- 1. 客户端发起文件访问请求

- 2. 接入层接收请求:

	- Nginx 检查是否可直接提供(静态文件)

	- 需验证/处理的请求转发给应用层

- 3. 应用层处理访问请求:

	- 验证访问权限

	- 检查缓存是否命中

- 4. 服务层获取文件:

	- 查询文件元数据

	- 定位物理文件位置

- 5. 数据层读取文件内容

- 6. 应用层处理文件:

	- 动态处理(如调整尺寸)

	- 统计访问信息

## 2.系统架构图

