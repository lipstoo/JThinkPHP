# JThinkPHP Framework

JThinkPHP 是一个极简、高性能、现代化、高工程化水准的 PHP 开发框架。它秉承“简捷（Just Think）”的设计理念，通过严谨的目录分层与 PSR-4 标准的命名空间管理，在保持极致轻量的同时，提供了现代企业级开发所需的全栈核心组件。

---

## 核心特性

- **高度工程化**：采用模块化目录结构（Foundation, Database, Http, Support, View），职责分明，易于扩展。
- **极致轻量**：核心代码极其精简，无第三方沉重依赖，启动速度处于行业领先水平。
- **DI 容器**：内置基于反射的依赖注入容器，支持构造函数自动解析、单例模式及闭包绑定。
- **多数据库驱动**：统一的 ORM 抽象层，内置 MySQL, PostgreSQL, SQLite, SQL Server 四大适配器。
- **安全体系**：内置 JWT 无状态认证、API 授权中间件及无缝的 CSRF 防护机制。
- **全能辅助**：集成 Redis 客户端、异步队列 (Queue)、多协议邮件发送 (Mailer)、精密数据验证 (Validator) 及文件上传组件。
- **CLI 命令行**：强大的 `jthink` 工具链，涵盖开发服务器、缓存管理、数据库迁移等全生命周期任务。

---

## 目录结构说明

```text
/
├── app/                    # 应用程序业务逻辑
│   ├── config/             # 应用配置文件 (route, database, session, queue 等)
│   ├── Controller/         # 控制器 (命名空间: App\Controller)
│   ├── Model/              # 数据模型 (建议继承 JThink\Core\Database\Model)
│   └── views/              # 视图模板 (支持布局与局部视图)
├── config/                 # 框架引导配置
├── core/                   # 框架核心源码 (命名空间: JThink\Core)
│   ├── Database/           # 数据库核心 (ORM, QueryBuilder, Schema, Adapters)
│   ├── Foundation/         # 基础引导 (JThink 引擎、全局助手函数)
│   ├── Http/               # 网络处理 (Router, Request, Response, Middleware)
│   ├── Support/            # 工具类库 (Container, JWT, Logger, Queue, Validator 等)
│   └── View/               # 视图引擎核心
├── database/               # 数据库相关文件
│   └── migrations/         # 数据库迁移脚本
├── public/                 # Web 入口及公开资源
│   ├── static/             # 静态资源 (CSS, JS, Images)
│   └── index.php           # 唯一入口文件
├── storage/                # 动态存储目录 (logs, cache, uploads)
├── .env                    # 环境配置文件 (本地敏感信息存放)
└── jthink                  # CLI 命令行交互入口脚本
```

---

## 安装与配置

1.  **环境要求**：PHP 7.4 或更高版本，需开启 PDO 扩展及对应的数据库驱动插件。
2.  **初始化配置**：
    复制并编辑项目根目录下的 `.env` 文件，配置数据库、Redis 及 `APP_KEY`：
    ```bash
    cp .env.example .env
    ```
3.  **目录权限**：确保 `storage/` 及其子目录具备可写权限，用于存放日志与缓存。

---

## CLI 命令行工具使用指南

JThink 提供了 `php jthink` 工具集，是您开发过程中的得力助手：

### 1. 启动开发环境
```bash
php jthink serve [port] # 默认端口 8000
```

### 2. 路由与性能优化
*   **查看路由列表**：`php jthink route:list`
*   **清理编译视图**：`php jthink view:clear`
*   **清理路由解析缓存**：`php jthink route:clear`
*   **清理数据库查询缓存**：`php jthink cache:clear`
*   **全量优化项清理**：`php jthink optimize:clear`

### 3. 数据库版本管理 (Migration)
*   **执行所有迁移**：`php jthink migrate`
*   **强制覆盖执行**：`php jthink migrate --force`
*   **回滚上一批次**：`php jthink migrate:rollback`

---

## 核心架构详解

### 核心分层设计

1.  **Foundation (基础层)**:
    *   `JThink.php`: 框架内核，负责服务加载、别名映射及 autoloader 注册。
    *   `functions.php`: 提供 `env()`, `config()`, `dd()`, `request()`, `view()` 等快捷全局函数.

2.  **Database (数据层)**:
    *   **Model (ORM)**: 实现 ActiveRecord 模式，封装 CRUD 逻辑.
    *   **QueryBuilder**: 支持链式调用的 SQL 构造器，内置查询缓存机制.
    *   **Schema**: 声明式的表结构管理 API，通过 `Blueprint` 定义字段.

3.  **Http (交互层)**:
    *   **Router**: 支持 RESTful 资源路由、路由分组及正则参数匹配.
    *   **Middleware**: 灵活的请求过滤管道，支持 API 认证、CSRF 校验等.

4.  **Support (支撑层)**:
    *   **JWT**: 提供 HS256 算法的 Token 生成与验证.
    *   **Queue**: 支持 `sync`, `redis`, `database` 三种驱动的任务队列.
    *   **Container**: 整个框架的“心脏”，管理所有服务的依赖注入.

---

## 开发规范与提交

- **命名规范**：遵循 PSR-12 代码风格。类名使用大驼峰，文件名与类名保持一致 (主思想 + 不超过三个单词的简捷命名)。
- **分支策略**：
  - `main`: 生产稳定版（正式发布），仅接受审核后的代码。
  - `dev`: 开发分支，提交前请确保已完成本地测试。
- **贡献流程**：所有修改请提交 Pull Request，严禁直接在 `main` 分支进行破坏性实验。

---
© 2026 JThinkPHP Team. 基于 MIT 协议开源。让 PHP 开发回归本质，Just Think.
