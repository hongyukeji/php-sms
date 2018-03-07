# PHP 短信扩展组件 - [鸿宇科技](http://www.hongyuvip.com/)

> PHP 短信扩展组件，目前支持阿里短信、云片短信，并且支持批量发送短信。设置默认短信后，会自动切换短信服务商。

> 碎碎念：没啥好说的，别人写的扩展组件，总是没有自己写的用起来顺手。方便自己，造福他人。

[![Latest Stable Version](https://poser.pugx.org/hongyukeji/php-sms/v/stable.png)](https://packagist.org/packages/hongyukeji/php-sms)
[![Total Downloads](https://poser.pugx.org/hongyukeji/php-sms/downloads.png)](https://packagist.org/packages/hongyukeji/php-sms)


## 安装

```
# 生产环境
$ composer require hongyukeji/php-sms

# 开发环境
$ composer require hongyukeji/php-sms dev-master
```

## 配置

* main.php / web.php 

> 阿里短信教程 https://help.aliyun.com/document_detail/59210.html

> 云片短信教程 https://www.yunpian.com/doc/zh_CN/introduction/demos/php.html


## 使用

> 具体使用，请阅读 src/SendSms.php 文件

```php

$config = [
    'defaultSms' => '',    // 默认短信
    'debug' => 'false',   // 调试模式
    'aliSms' => [
        'accessKeyId' => '',
        'accessKeySecret' => '',
        'signName' => '',
        'templateCode' => [
            'verificationCode' => '',
        ],
    ],
    'yunpianSms' => [
        'apikey' => '',
        'signName' => '',
        'templateCode' => [
            'verificationCode' => '',
        ],
    ],
];

$smsObj = new SendSms($config);

$result = $smsObj->send(
    'verificationCode',
    '13800138000',
    ['code' => '123456', 'product' => 'name']
);

if ($result['code'] == '0') {
    echo '发送成功';
} else {
    echo '发送失败: ' . $result['message'];
}

// 手机号为数组格式，可以批量发送短信，如：['13800138000','13900139000']
// 返回格式: ['code'=>'...','message'=>'...']
// code返回码说明: 0-发送成功, 1-发送失败
```

## 关于

* Site：www.hongyuvip.com
* Author：Shadow
* QQ：1527200768
* Phone：13952101395
* Email：admin@hongyuvip.com