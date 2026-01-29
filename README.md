# Hoshino Cursor

**中文说明**

Hoshino Cursor 是一个 WordPress 插件，用于在前端实现自定义 SVG 光标，并提供点击时的粉色粒子爆裂效果。插件内置可视化管理面板，支持上传或粘贴 SVG，并对粒子数量、颜色、速度进行调整。

## 版本
- 1.0.0

## 兼容性
- WordPress 5.0+
- PHP 7.2+

## 功能特性
- 常规态与输入态两套 SVG 光标
- 后台面板上传/粘贴 SVG（粘贴优先）
- 点击爆裂粉色粒子，支持数量/颜色/速度调整
- 光标尺寸自动约束到 24–40px，超出则按 1/2 递归缩小

## 安装方法
1. 将除了readme文件和hoshino cursor.php之外的所有文件放入一个同目录下的assets文件夹，并将插件目录放入 `wp-content/plugins`
2. 在 WordPress 后台启用插件：`Hoshino Cursor`
3. 进入后台菜单：`Hoshino光标插件设置`

## 使用方法
1. 在 `Hoshino光标插件设置` 中上传或粘贴两套 SVG
2. 设置常规态/输入态启用开关
3. 根据需要调整粒子数量、颜色与速度
4. 保存后刷新前端页面即可生效

## 设置项说明
- **常规态SVG上传/粘贴**：常规状态光标的 SVG
- **输入态SVG上传/粘贴**：输入框/文本区域的光标 SVG
- **启用常规态光标**：开启/关闭常规态光标
- **启用输入态光标**：开启/关闭输入态光标
- **粒子数量**：范围 4–20（默认 10）
- **粒子颜色**：默认 `#ff5fa2`
- **粒子速度**：范围 200–800ms（默认 420ms）

## SVG 安全说明
插件会对 SVG 做严格白名单清洗，过滤脚本与高风险标签。
建议只使用可信来源的 SVG 文件。

## 欢迎页说明
首次进入设置页会显示欢迎页，点击 OK 会：
- 进入插件设置面板
- 打开新页面：`https://blog.dengfangbo.com`
- 仅首次出现，后续不再显示

## 目录结构
```
hoshino-cursor/
├─ assets/
│  ├─ admin.js
│  ├─ cursor.css
│  └─ cursor.js
├─ hoshino-cursor.php
└─ README.md
```

## 卸载说明
停用并删除插件即可。

---

**English**

Hoshino Cursor is a WordPress plugin that provides custom SVG cursors and a pink particle burst on click. It includes an admin settings panel to upload/paste SVGs and adjust particle settings.

## Version
- 1.0.0

## Compatibility
- WordPress 5.0+
- PHP 7.2+

## Features
- Two cursor states: main and input
- Upload or paste SVG (paste takes priority)
- Click burst particles with adjustable count/color/speed
- Cursor size clamped to 24–40px with recursive 1/2 scaling

## Installation
1. Place all files except the readme file and hoshino cursor.php into the assets folder in the same directory, and move the plugin directory to `wp-content/plugins`.
2. Activate the plugin in WP admin: `Hoshino Cursor`
3. Open the menu: `Hoshino光标插件设置`

## Usage
1. Upload or paste both SVGs
2. Toggle main/input cursor on/off
3. Adjust particle count, color, and speed
4. Save and refresh the frontend

## Settings
- **Main SVG Upload/Paste**: SVG for default cursor
- **Input SVG Upload/Paste**: SVG for input/textarea cursor
- **Enable Main Cursor**
- **Enable Input Cursor**
- **Particle Count**: 4–20 (default 10)
- **Particle Color**: `#ff5fa2`
- **Particle Speed**: 200–800ms (default 420ms)

## SVG Security
SVG content is sanitized with a strict allowlist. Use trusted SVG files only.

## Welcome Page
Shown only once when opening the settings page for the first time. Clicking OK:
- Opens the plugin settings page
- Opens `https://blog.dengfangbo.com` in a new tab
- Will not appear again after confirmation

## Structure
```
hoshino-cursor/
├─ assets/
│  ├─ admin.js
│  ├─ cursor.css
│  └─ cursor.js
├─ hoshino-cursor.php
└─ README.md
```

## Author
Fangbo
Blog: https://blog.dengfangbo.com

