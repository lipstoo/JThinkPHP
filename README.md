# JThinkPHP Framework

JThinkPHP 是一个极简、高性能、现代化的 PHP 框架。它秉承“简捷（Just Think）”的设计理念，在保持极致轻量的同时，提供了现代企业级开发所需的核心组件。

## 核心特性
- **极致轻量**：核心代码极其精简，无冗余依赖，启动速度飞快。
- **DI 容器**：基于反射的依赖注入容器，支持自动注入与单例模式。
- **全能数据库适配**：内置 MySQL, PostgreSQL, SQLite, SQL Server 四大驱动。
- **现代化设计**：内置 JWT, Redis, Queue, Mailer, Validator 等常用模块。
- **CLI 命令行**：强大的 `jthink` 命令行工具，提升开发效率。
- **现代 UI**：配套 Glassmorphism（玻璃拟态）风格的前端组件库。

---

## 目录结构说明

```text
/
├── app/                # 应用程序核心目录
│   ├── config/         # 应用配置文件 (route, database, session, queue 等)
│   ├── controller/     # 控制器目录 (App\Controller 命名空间)
│   ├── model/          # 模型目录 (建议继承 JThink\Core\Model)
│   └── view/           # 视图模板目录
├── config/             # 全局系统配置
├── core/               # 框架核心源代码 (JThink\Core 命名空间)
│   ├── db/             # 数据库驱动适配器
│   ├── middleware/     # 内置中间件 (如 CsrfMiddleware)
│   └── ...             # 核心组件 (Request, Response, Router, Container 等)
├── facade/             # 门面类，提供静态调用入口
├── public/             # 公共入口及静态资源
│   ├── css/            # JThink UI 样式
│   ├── js/             # JThink UI 脚本
│   └── index.php       # Web 程序入口
├── storage/            # 存储目录 (缓存、日志、上传文件)
├── .env                # 环境配置文件 (敏感信息存放处)
└── jthink              # CLI 命令行工具入口
```

---

## 安装与配置

1. **环境要求**：PHP 7.4+，安装了 PDO 及对应数据库驱动。
2. **配置环境**：
   复制并修改 `.env` 文件中的数据库、Redis 及其他配置项：
   ```bash
   cp .env.example .env # 如果存在 example
   ```
3. **设置权限**：确保 `storage` 目录具备写权限。

---

## CLI 命令行工具使用指南

JThink 提供了 `php jthink` 工具集，用于辅助开发与系统维护：

### 1. 开发服务
启动内置开发服务器：
```bash
php jthink serve [port] # 默认端口 8000
```

### 2. 路由管理
查看当前系统注册的所有路由：
```bash
php jthink route:list
```

### 3. 缓存清理 (优化建议)
框架为了性能会生成各种缓存，可以使用以下命令清理：

*   **清理编译后的视图**：
    ```bash
    php jthink view:clear
    ```
*   **清理路由缓存**：
    ```bash
    php jthink route:clear
    ```
*   **清理数据库查询/数据缓存**：
    ```bash
    php jthink cache:clear
    ```
*   **清理所有优化项（一键清理）**：
    ```bash
    php jthink optimize:clear
    ```

### 4. 数据库迁移
运行数据库迁移脚本：
```bash
php jthink migrate
# 强制运行 (生产环境或覆盖操作)
php jthink migrate --force
```

---

## 核心文件功能详解

### `core/JThink.php`
框架的总控中心，负责引导程序启动、加载环境配置、注册自动加载器及初始化核心容器。

### `core/Container.php`
依赖注入容器，支持 `singleton`, `bind`, `make`, `call` 等操作。

### `core/Request.php` & `core/Response.php`
封装了 HTTP 的输入与输出。你可以通过 `request()` 获取参数，通过 `response()` 发送响应。

### `core/Model.php`
基础模型类。通过继承它，你的模型可以获得 `find($id)`, `all()`, `save()`, `delete()` 等简捷的 CRUD 方法。

### `core/QueryBuilder.php`
流式 SQL 构建器。支持链式操作：`DB::table('users')->where('id', 1)->first();`。

---

## 贡献与规范
- **正式发布**：代码必须推送到 `main` 分支。
- **开发提交**：所有新特性必须推送到 `dev` 分支，并通过 Pull Request 进行审核。
- **命名规范**：主意思 + 不超过三个单词的简捷命名。

---
© 2024 JThinkPHP Team. 基于 MIT 协议开源。
