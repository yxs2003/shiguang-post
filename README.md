## Shiguang Post (时光机文章)
A WordPress plugin that scans for and utilizes unused post IDs, allowing you to create new posts with specific, non-sequential IDs.

一个可以扫描并利用 WordPress 中被跳过的文章 ID 的插件，让你能够用这些“尘封”的 ID 创建新的文章。

## 📖 简介 (Description)

#### 🤔 什么是“文章 ID 空缺”？
在 WordPress 中，每一篇文章、页面、草稿、甚至是修订版本，都会在数据库的 wp_posts 表中占据一个独一无二的 ID。这个 ID 是自动递增的，并且永远不会被重复使用。举个例子，假如你创建了一篇草稿（比如 ID 是 100），然后删除了它，那么下一篇发布的文章 ID 将会是 101，而 ID 100 就永远地成为了你数据库中的一个“空缺”。

### ✨ 这个插件是做什么的？
Shiguang Post (时光机文章) 会扫描你的整个文章数据库，找出这些被跳过、已删除或未被使用的文章 ID。然后，它会为你呈现一个清晰的可用 ID 列表。

你只需轻轻一点，就可以选择任意一个空缺的 ID 来创建一篇新的草稿。对于有特殊 SEO 需求、希望维持特定文章 ID 结构，或者仅仅是想“填补”数据库空白的用户来说，这个插件是绝佳的选择。

### ⭐ 主要功能
扫描可用 ID：自动扫描从 1 到最新文章 ID 之间的所有空缺 ID。

一键创建草稿：选择一个心仪的 ID，插件会立刻为你创建一篇标题为 idXX 的新草稿。

美观易用的界面：在 WordPress 后台“工具”菜单下，拥有一个精心设计、简单直观的操作页面。

安全可靠：使用 WordPress 原生的 AJAX 和 Nonce 安全机制，无需刷新页面即可完成操作。

无冲突：采用独特的前缀命名，避免与其他插件发生冲突，保证网站稳定。

### 🚀 安装 (Installation)
Download: Download the .zip file from the GitHub repository's "Releases" section.

下载: 从本 GitHub 仓库的 "Releases" 页面下载最新的 .zip 压缩包。

Upload to WordPress: Log in to your WordPress admin panel, navigate to Plugins > Add New > Upload Plugin, and upload the downloaded .zip file.

上传至 WordPress: 登录你的 WordPress 后台，转到 插件 > 安装插件 > 上传插件，然后上传你刚刚下载的 .zip 文件。

Activate: Activate the "Shiguang Post" plugin.

启用: 启用 “Shiguang Post” 插件。

### 🛠️ 如何使用 (How to Use)
After activation, go to Tools > Shiguang Post (工具 > 时光机文章) in your WordPress admin dashboard.

启用插件后，在后台左侧菜单找到 工具 > 时光机文章。

You will see a list of all available post IDs.

页面上会列出所有扫描到的可用 ID。

Click the Create Draft (创建草稿) button next to the ID you want to use.

找到你想要的 ID，点击旁边的 创建草稿 按钮。

A success message will appear, and a new draft post will be created with that ID. You can find it under Posts > All Posts (usually in the "Drafts" tab).

操作成功后，系统会提示你。你可以在 文章 > 所有文章 的“草稿”分类中找到这篇新文章。

### 📸 截图 (Screenshots)
设置页面

[![设置页面](https://s21.ax1x.com/2025/11/12/pZCwWeH.webp)](https://imgchr.com/i/pZCwWeH)
草稿效果

[![草稿效果](https://s21.ax1x.com/2025/10/01/pVTuXP1.jpg)](https://imgse.com/i/pVTuXP1)

### 🍃 付费版本 (Pro)

1.重构UI为玻璃圆角拟态+多TAB设置菜单
2.可以单独选择某一个id创建草稿+多选id批量创建草稿
3.扩展数据库清理范围

插件价格6.6，购买联系邮箱2977443056@qq.com

您的赞助是我更新的最大动力！

[![插件首页](https://s21.ax1x.com/2025/11/12/pZCw7Sf.jpg)](https://imgchr.com/i/pZCw7Sf)
[![数据清理](https://s21.ax1x.com/2025/11/12/pZCwofP.jpg)](https://imgchr.com/i/pZCwofP)
[![文章ID页](https://s21.ax1x.com/2025/11/12/pZCwHl8.jpg)](https://imgchr.com/i/pZCwHl8)

### 📄 授权 (License)
This plugin is licensed under the GPL v2 or later.
本插件基于 GPL v2 或更高版本许可证开源。
