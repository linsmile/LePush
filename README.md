## Installation

#### 使用 Composer 安装

- 在项目中的 `composer.json` 文件中添加 jpush 依赖：

```json
"require": {
    "lepush/lepush": "*"
}
```

- 执行 `$ php composer.phar install` 或 `$ composer install` 进行安装。

#### 直接下载源码安装

> 直接下载源代码也是一种安装 SDK 的方法，不过因为有版本更新的维护问题，所以这种安装方式**十分不推荐**，但由于种种原因导致无法使用 Composer，所以我们也提供了这种情况下的备选方案。

- 下载源代码包，解压到项目中
- 在项目中引入 autoload：

```php
require 'path_to_sdk/autoload.php';
```
test``