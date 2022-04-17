## 简介
插件化开发，安装插件时很多情况需要修改配置文件，通过正则能完美解决修改配置影响备注信息的问题。
`taoser/think-setarr`通过配置数组，即可非常简便的修改、添加、删除配置文件中的数据，使得插件化开发更加统一和灵活的对配置文件的操作。

>配置文件数组遵守的规则为：
1. 一个数组中，无论它有多少个元素及是否包含子数组，它的索引数组(一维)在前，关联(多维)数组排序在后。
2. 数组每个元素后加`，`号，数组结尾`],`逗号，以方便需要插入新元素时识别。

## 安装
```php
composer require taoser/think-setarr
```

## 使用
> 类引用变量`app`即为config/app.php文件

```html
use taoser\SetArr;

	$data = [
		true,false,111,
		'aaa'=>'bbb',
		'ccc'=>[
			true,
			222,
			"app()";
			"//备注信息",
			......
		],
		......
	];
	$result = SetArr::name('app')->add($data);
```

### 添加数组节点

> 可以对配置文件数组中添加新的元素，添加规则：
1. 支持嵌套4级数组，最后一级数组元素只能是一维数组，
2. 只能给一维数组元素添加备注`“//备注”`，不能添加在关联数组的前面,
3. 函数，类，需要引号包裹`""`,
4. bool，数值，字符串非索引的元素的添加会重复添加，
5. 结果返回布尔值

```php
$add = [
    1,true,false,
    "//支持备注的添加",
    "base_path() . DIRECTORY_SEPARATOR . 'public',"
    'a'    => 1,
    'b'    => 'b',
    'c'    => true,
    'd'    => false,
    'e'    => "support\bootstrap\Session::class,",
	"// 这里这一行添加备注是无效的，在子数组前写备注位置会不正确",
    'f'    => [
        22,true,false,
        'aa'    => 11,
        'bb'    => 'bb',
        'cc'    => true,
        'dd'    => false,
        "//支持备注的添加",
        'ee'    => "base_path() . DIRECTORY_SEPARATOR . 'public',"
        'ff'    => [
            333,true,false,
            'aaa'    => 11,
            'bbb'    => 'bb',
            'ccc'    => true,
            'ddd'    => false,
            'eee'    => "support\bootstrap\Session::class,",
            'fff'    => [
                "//支持备注的添加，最后一级数组只能是一维数组",
                'aaaa'    => 11,
                'bbbb'    => 'bb',
                'cccc'    => true,
                'dddd'    => false,
                'eeee'    => "support\bootstrap\Session::class,",
            ],
        ],
        'gg'    => [
            1,true,false,
            "base_path() . DIRECTORY_SEPARATOR . 'public',"
            'aa'    => 11,
            'bb'    => 'bb',
            'cc'    => true,
            'dd'    => false,
            'ee'    => "support\bootstrap\Session::class,",
        ],
    ],
    'g'    => [
        1,true,false,
        'ga'    => 11,
        'gb'    => 'bb',
        'gc'    => true,
        'gd'    => false,
        'ge'    => "support\bootstrap\Session::class,",
        'gf'    => [
            1,true,false,
            "base_path() . DIRECTORY_SEPARATOR . 'public',"
            'gaa'    => 11,
            'gbb'    => 'bb',
            'gcc'    => true,
            'gdd'    => false,
            'gee'    => "support\bootstrap\Session::class,",
        ],
    ],
];

$conf = \taoser\SetArr::name('app')->add($data);

```

### 编辑数组节点

> 数组编辑，
1. 不能直接对非索引(下标0，1数值型索引)元素数组进行操作，
2. 索引数组的编辑需要先`delete`删除(如true,111,"app()","//备注")，再`add`添加value(如，false,12345,"support\bootstrap\Session::class,"，"备注2")。

```php
use taoser\SetArr;

$edit = [
    'a'    => 1,
    'b'    => 'b',
    'c'    => true,
    'd'    => false,
    'e'    => "support\bootstrap\Session::class,",
    'f'    => [
        'aa'    => 11,
        'bb'    => 'bb',
        'cc'    => true,
        'dd'    => false,
        'ee'    => "base_path() . DIRECTORY_SEPARATOR . 'public',"
        'ff'    => [
            'aaa'    => 11,
            'bbb'    => 'bb',
            'ccc'    => true,
            'ddd'    => false,
            'eee'    => "support\bootstrap\Session::class,",
            'fff'    => [
                'aaaa'    => 11,
                'bbbb'    => 'bb',
                'cccc'    => true,
                'dddd'    => false,
                'eeee'    => "support\bootstrap\Session::class,",
            ],
        ],
        'gg'    => [
            1,true,false,
            "base_path() . DIRECTORY_SEPARATOR . 'public',"
            'aa'    => 11,
            'bb'    => 'bb',
            'cc'    => true,
            'dd'    => false,
            'ee'    => "support\bootstrap\Session::class,",
        ],
    ],
    'g'    => [
        'ga'    => 11,
        'gb'    => 'bb',
        'ge'    => "support\bootstrap\Session::class,",
        'gf'    => [
            'gaa'    => 11,
            'gbb'    => 'bb',
            'gcc'    => true,
            'gdd'    => false,
            'gee'    => "support\bootstrap\Session::class,",
        ],
    ],
];

$conf = SetArr::name('app')->edit($edit);

```

### 删除数值

> 删除元素规则
1. “//备注”、bool、111、'abcd'等,给定value即会删除；
2. 关联数组需要给定key和value，value值为空,0,false,null均会删除。
3. 删除数据是一条一条删除的，不能只给一个数组的key
4. 一个数组删除后会空会把空数组删除

```php
use taoser\SetArr;

$del = [
    true,false,111,'US-A',
	"// 删除备注",
	"base_path() . DIRECTORY_SEPARATOR . 'public',",
    'a'    => 0,
    'b'    => 0,
    'c'    => 0,
    'd'    => 0,
    'e'    => 0,
    'f'    => [
        'aa'    => '',
        'bb'    => '',
        'cc'    => '',
        'dd'    => '',
        'ee'    => ""
        'ff'    => [
            'aaa'    => false,
            'bbb'    => false,
            'ccc'    => false,
            'ddd'    => false,
            'eee'    => false,
            'fff'    => [
                'aaaa'    => null,
                'bbbb'    => null,
                'cccc'    => null,
                'dddd'    => null,
                'eeee'    => null,
            ],
        ],
];

$conf = SetArr::name('app')->delete($del);

```

详细参考 [作者](http://wiki.aieok.com)