---
name: dev-guide
description: Use when developing, testing, or extending yansongda/artful project. Covers commands, code standards, and plugin/packer/direction extension guide.
---

# yansongda/artful 开发指南

## 开发命令

**对 `src/` PHP 代码的任何修改，需运行以下三项检查：**

```bash
composer cs-fix && composer analyse && composer test
```

### 测试
```bash
composer test       # PHPUnit 9.x + Mockery 1.6
```
- 测试前需安装 `hyperf/pimple`（default 框架，`require-dev` 已包含）
- Mock HTTP 客户端避免真实请求

### 代码风格
```bash
composer cs-fix     # 代码格式化检查（dry-run）
```

### 静态分析
```bash
composer analyse    # PHPStan level 5
```

### 文档
```bash
cd web && pnpm web:dev      # 文档开发
cd web && pnpm web:build    # 文档构建
cd web && pnpm web:preview  # 本地预览
```

### 本地开发环境
优先本地 PHP 环境；若无 PHP，使用 Container 作为备选，详见 `container-dev` Skill。

### CI 矩阵
PHP 8.1-8.3 + Laravel/Hyperf/Default

---

## 代码规范

- `declare(strict_types=1);` 必须
- `use` 导入，禁止 `\Yansongda\Artful\...`
- 日志/异常消息用中文
- 命名：
  - 插件：`{Action}Plugin.php`（如 `AddRadarPlugin`、`ParserPlugin`）
  - 打包器：`{Type}Packer.php`（如 `JsonPacker`、`XmlPacker`）
  - 方向：`{Type}Direction.php`（如 `CollectionDirection`）
  - 服务提供者：`{Name}ServiceProvider.php`

---

## 核心架构

### 插件管道
```
StartPlugin -> [业务插件] -> AddRadarPlugin -> [HTTP 请求] -> ParserPlugin
```

每个插件实现 `PluginInterface::assembly(Rocket $rocket, Closure $next): Rocket`，通过 `$next($rocket)` 串联。

### 核心载体 Rocket
贯穿整条管道，承载：
- `params`：原始参数
- `payload`：待发送数据（`Collection`）
- `radar`：PSR-7 `RequestInterface`（HTTP 请求）
- `destination`：最终结果（`Collection` | `MessageInterface`）
- `destinationOrigin`：原始响应（`RequestInterface` | `ResponseInterface`）

### 扩展点
| 类型 | 契约 | 职责 |
|------|------|------|
| Plugin | `PluginInterface` | 管道节点，组装/解析请求响应 |
| Packer | `PackerInterface` | payload 序列化/反序列化（`pack`/`unpack`） |
| Direction | `DirectionInterface` | 响应解析方向（`guide`） |
| Shortcut | `ShortcutInterface` | 快捷方式，返回插件列表（`getPlugins`） |

### HTTP 客户端
`HttpClientFactory` 优先从容器获取 `HttpClientInterface`（PSR-18），否则创建 `GuzzleHttp\Client`。默认依赖 `guzzlehttp/guzzle`（7.x 或 8.x），需用户自行安装。

---

## 新增插件流程

1. 在 `src/Plugin/` 创建 `{Name}Plugin.php`，实现 `PluginInterface`
2. `assembly()` 中操作 `Rocket`（组装 payload、设置 radar 等），并 `return $next($rocket)`
3. 通过 `Artful::artful([插件...], $params)` 串联执行，或封装为 `Shortcut`
4. 补充测试：`tests/Plugin/{Name}PluginTest.php`，Mock HTTP 客户端
5. 运行 `composer cs-fix && composer analyse && composer test`

---

## 常见错误

- 忽略 `declare(strict_types=1);`
- 直接写完整命名空间而非 `use` 导入
- 插件 `assembly()` 忘记 `return $next($rocket)`，导致管道中断
- 测试未 Mock HTTP 客户端导致真实网络请求
- 修改 `src/` 后未跑 `cs-fix`/`analyse`/`test` 三项检查
