ThinkPHP 调试输出扩展
--------------------
支持本地和远程调试输出

![](https://github.com/user-attachments/assets/a9979d5a-2ac9-41b4-ad35-92e52858526a)

## 安装
```
composer require topthink/think-dumper --dev
```

环境要求 `PHP8.0+` 

## 调试
打开浏览器访问 https://developer.topthink.com/thinkphp/dumper 

点击左侧菜单 生成令牌并复制令牌内容

修改`.env` 环境变量文件，增加

```
DUMPER_TOKEN = 令牌值
```

然后 在代码中使用助手函数输出变量调试
```
dump($var...)
```

调试内容会在 
https://developer.topthink.com/thinkphp/dumper 中显示

如果没有设置令牌或远程打印页面没有打开，则`dump`函数只会在本地输出调试内容

> 请放心，变量内容不会经过服务器，只会保存在本地浏览器，并且可以随时清除。
