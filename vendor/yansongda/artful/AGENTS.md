# yansongda/artful AGENTS.md

## OVERVIEW
PHP API 请求框架（Api RequesT Framework U Like）。基于插件管道架构，抽离请求逻辑与过程，统一管理 API 调用。符合 PSR-2/3/4/7/11/14/18 标准，便于与 Laravel、Hyperf 等任意框架集成。是 `yansongda/pay` 等上层 SDK 的底座。

## STRUCTURE
```
src/
├── Contract/        # 核心契约（Plugin/Packer/Direction/Shortcut/HttpClient 等）
├── Direction/       # 响应解析方向（Collection/Response/OriginResponse/NoHttpRequest）
├── Event/           # PSR-14 事件（ArtfulStart/ArtfulEnd/HttpStart/HttpEnd）
├── Exception/       # 异常与错误码
├── Packer/          # 打包器（Json/Query/Xml）
├── Plugin/          # 插件（Start/AddPayloadBody/AddRadar/Parser）
├── Service/         # 服务提供者（容器注册）
├── Artful.php       # 核心入口
├── Rocket.php       # 请求载体
├── HttpClientFactory.php
├── Functions.php    # 辅助函数
└── Logger.php
```

## 核心组件
| 符号 | 位置 | 用途 |
|---|---|---|
| `Artful` | `Artful.php` | 核心入口（`config`/`artful`/`shortcut`/`ignite`/`make`/`get`） |
| `Rocket` | `Rocket.php` | 请求载体（params/payload/radar/destination） |
| `PluginInterface` | `Contract/` | 插件契约（`assembly`） |
| `PackerInterface` | `Contract/` | 打包器契约（`pack`/`unpack`） |
| `DirectionInterface` | `Contract/` | 响应方向契约（`guide`） |
| `ShortcutInterface` | `Contract/` | 快捷方式契约（`getPlugins`） |
| `HttpClientFactory` | `HttpClientFactory.php` | PSR-18 客户端工厂 |
| `Event` | `Event.php` | PSR-14 事件分发 |

## 核心原则
- **PSR 标准**：严格遵循 PSR-7/11/14/18，便于框架集成与替换
- **插件管道**：请求/响应逻辑由可组合的插件承载，一个文件一个插件，API 间互不影响
- **一次配置多次使用**：`Artful::config()` 统一配置，后续调用零配置

## COMMANDS & 开发规范
详见 `.agents/skills/dev-guide/SKILL.md`，使用时加载。容器开发环境见 `.agents/skills/container-dev/SKILL.md`。

## 架构要点
- **插件管道**：`StartPlugin -> [业务插件] -> AddRadarPlugin -> [HTTP 请求] -> ParserPlugin`，插件经 `assembly(Rocket, Closure $next)` 串联，必须 `return $next($rocket)`
- **Rocket 载体**：贯穿管道，承载 `radar`（PSR-7 `RequestInterface`）、`payload`（`Collection`）、`params`、`destination`、`destinationOrigin`
- **HTTP 客户端**：`HttpClientFactory` 优先取容器中的 `HttpClientInterface`（PSR-18），否则创建 `GuzzleHttp\Client`；默认依赖 `guzzlehttp/guzzle`（7.x 或 8.x），需用户自行安装
- **Packer**：负责 payload 序列化（Json/Query/Xml），通过 `pack`/`unpack` 双向转换
- **Direction**：决定响应如何解析为 `destination`（Collection / 原始 Response / 无 HTTP 请求等）
- **事件系统**：PSR-14，`ArtfulStart`（管道开始）、`HttpStart`（请求前）、`HttpEnd`（响应后）、`ArtfulEnd`（管道结束）

## 依赖
- `guzzlehttp/psr7: ^2.6 || ^3.0`（PSR-7 实现，兼容 guzzle 7.x/8.x）
- `psr/http-client: ^1.0`、`psr/http-message: ^1.1 || ^2.0`
- `psr/container: ^1.1 || ^2.0`、`psr/event-dispatcher: ^1.0`、`psr/log: ^1.1 || ^2.0 || ^3.0`
- `yansongda/supports: ~4.0.10`
- PHP `>=8.0`；CI 矩阵：PHP 8.1-8.3 + Laravel/Hyperf/Default
