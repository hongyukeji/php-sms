# PhpSms - SMS - 短信发送，从未如此简单

> 全网首款支持所有短信服务商，自由扩展，无缝对接。

> 采用Hongyukeji专利[《宏观设计模式》](docs/README.md)开发。

> 更多请点击 [https://www.hongyukeji.cn](https://www.hongyukeji.cn)

<p align="center">
<a href="https://packagist.org/packages/hongyukeji/sms"><img src="https://poser.pugx.org/hongyukeji/sms/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/hongyukeji/sms"><img src="https://poser.pugx.org/hongyukeji/sms/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/hongyukeji/sms"><img src="https://poser.pugx.org/hongyukeji/sms/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/hongyukeji/sms"><img src="https://poser.pugx.org/hongyukeji/sms/license" alt="License"></a>
</p>

## 特点

- 支持全网络短信服务商
- 支持自由扩展且易维护

## 支持

- [阿里云](https://www.aliyun.com)
- [云片网](https://www.yunpian.com)
- [腾讯云](https://cloud.tencent.com/product/sms)
- [短信宝](http://www.smsbao.com)
- [赛邮云通信](https://www.mysubmail.com)
- [SendCloud](https://www.sendcloud.net)
- [互亿无线](http://www.ihuyi.com)
- [创蓝253](https://www.253.com)
- [螺丝帽](https://luosimao.com)
- [华信云通信](https://www.ipyy.com)
- [华为云](https://www.huaweicloud.com/product/msgsms.html)
- [百度云](https://cloud.baidu.com)
- [七牛云](https://www.qiniu.com/products/sms)
- [云之讯](https://www.ucpaas.com/index.html)
- [凯信通](http://www.kingtto.cn)
- [聚合数据](https://www.juhe.cn)
- [阿凡达数据](https://www.avatardata.cn)
- [网易云信](https://yunxin.163.com/sms)
- [容联云通讯](https://www.yuntongxun.com)
- [优刻得](https://www.ucloud.cn)
- 上述短信服务商比较常用，其他短信如有需要可联系[Hongyukeji](http://wpa.qq.com/msgrd?v=3&uin=1275588965&site=qq&menu=yes)集成
- 如需支持其他短信服务商，可以自行Fork，在`src/Sms.php`中添加对应的短信发送方法即可
- 短信快速集成（参考[《宏观设计模式》](docs/README.md) — Hongyukeji出品）

## 环境

- PHP >= 5.3.0

## 安装

```shell
$ composer require hongyukeji/php-sms
```

## 使用

> PHP框架中使用，配置文件参考 'config/sms.php' 

> 短信发送参数详解: send([参数1-手机号: 支持字符串和数组格式],[参数2-模板: 支持模板Code和模板内容],[参数3-模板参数: 模板对应的参数, 数组格式],[参数4-短信服务商: 设置短信发送服务商, 该参数为空时调用配置文件中默认短信服务商])

```
use Hongyukeji\PhpSms\Sms;

$config = [
    'default' => 'aliyun',
    'gateways' => [
        'yunpian' => [
            'apikey' => '',
        ],
        'aliyun' => [
            'accessKeyId' => '',
            'accessKeySecret' => '',
            'signName' => '',
        ],
        'qcloud' => [
            'appid' => '',
            'appkey' => '',
            'smsSign' => '',
        ],
        'duanxinbao' => [
            'user' => '',
            'pass' => '',
            'signName' => '',
        ],
        'submail' => [
            'appid' => '',
            'appkey' => '',
        ],
        'sendcloud' => [
            'sms_user' => '',
            'sms_key' => '',
        ],
        'ihuyi' => [
            'apiid' => '',
            'apikey' => '',
        ],
        // Other...
    ],
];

// 初始化短信类
$sms = new Sms($config);

// 模板CODE + 模板参数 
// template_code + template_params
$result_template_code = $sms->send('13800138000', 'SMS_88888888', [
    'code' => '1234',
    'time' => '15分钟',
]);
var_dump($result_template_code);

// 模板内容 + 模板参数
// template_content + template_params
$result_template_content = $sms->send(['13800138000', '13900139000'], '您的验证码是%s。有效期为%s，请尽快验证！', [
    'code' => '1234',
    'time' => '15分钟',
], 'duanxinbao');
var_dump($result_template_content);

// $result 结果返回值说明
// code: 0 => 短信发送成功, 1 => 短信发送失败
$result = [
    'code' => '0',
    'message' => '短信发送成功！',
];
```

## 维护

- Author：Hongyukeji
- QQ：[1275588965](http://wpa.qq.com/msgrd?v=3&uin=1275588965&site=qq&menu=yes)
- Email：[hongyukeji@qq.com](mailto:hongyukeji@qq.com)

## 配置

- [阿里云](https://help.aliyun.com/document_detail/55451.html)

> 使用说明：template_code + template_params

```
'aliyun' => [
    'accessKeyId' => '',
    'accessKeySecret' => '',
    'signName' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\AliyunGateway',
],
```

- [云片网](https://www.yunpian.com/doc/zh_CN/introduction/demos/php.html)

> 使用说明：template_code + template_params

```
'yunpian' => [
    'apikey' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\YunpianGateway',
],
```

- [腾讯云](https://cloud.tencent.com/document/product/382/9557)

> 使用说明：template_code + template_params

```
'qcloud' => [
    'appid' => '',
    'appkey' => '',
    'smsSign' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\QcloudGateway',
],
```

- [短信宝](http://www.smsbao.com/openapi/55.html)

> 使用说明：template_content + template_params

```
'duanxinbao' => [
    'user' => '',
    'pass' => '',
    'signName' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\DuanxinbaoGateway',
],
```

- [赛邮云通信](https://www.mysubmail.com/chs/documents/developer/t2f1J2)

> 使用说明：template_code + template_params

```
'submail' => [
    'appid' => '',
    'appkey' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\SubmailGateway',
],
```

- [SendCloud](https://www.sendcloud.net/doc/sms)

> 使用说明：template_code + template_params

```
'sendcloud' => [
    'sms_user' => '',
    'sms_key' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\SendCloudGateway',
],
```

- [互亿无线](http://www.ihuyi.com/demo/sms/php.html)

> 使用说明：template_content + template_params

```
'ihuyi' => [
    'apiid' => '',
    'apikey' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\IhuyiGateway',
],
```

- [创蓝253](https://zz.253.com/api_doc/)

> 使用说明：template_content + template_params

```
'chuanglan' => [
    'api_send_url' => '',
    'api_variable_url' => '',
    'api_balance_query_url' => '',
    'api_account' => '',
    'api_password' => '',
    'sms_sign' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\ChuanglanGateway',
],
```

- [螺丝帽](https://luosimao.com/docs/api/)

> 使用说明：template_content + template_params

```
'luosimao' => [
    'sms_name' => '螺丝帽',
    'sms_url' => 'https://luosimao.com',
    'api_key' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\LuosimaoGateway',
],
```

- [华信云通信](https://www.ipyy.com/help/)

> 使用说明：template_content + template_params

```
'huaxin' => [
    'sms_name' => '华信云通信',
    'sms_url' => 'https://luosimao.com',
    'account' => '',
    'password' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\LuosimaoGateway',
],
```

- [华为云短信](https://support.huaweicloud.com/api-msgsms/sms_05_0000.html)

> 使用说明：template_code + template_params

```
'huawei' => [
    'sms_name' => '华为云',
    'sms_url' => 'https://www.huaweicloud.com/product/msgsms.html',
    'app_key' => '',
    'app_secret' => '',
    'sms_sign' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\HuaweiGateway',
],
```

- [百度云](https://cloud.baidu.com/doc/SMS/s/Yjwvxrwzb)

> 使用说明：template_code + template_params

```
'baidu' => [
    'sms_name' => '百度云',
    'sms_url' => 'https://cloud.baidu.com/',
    'invokeId' => '',
    'ak' => '',
    'sk' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\BaiduGateway',
],
```

- [七牛云](https://developer.qiniu.com/sms/api/5897/sms-api-send-message)

> 使用说明：template_code + template_params

```
'qiniu' => [
    'sms_name' => '七牛云',
    'sms_url' => 'https://www.qiniu.com/products/sms',
    'access_key' => '',
    'secret_key' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\QiniuGateway',
],
```

- [云之讯](http://docs.ucpaas.com/doku.php)

> 使用说明：template_code + template_params

```
'yunzhixun' => [
    'sms_name' => '云之讯',
    'sms_url' => 'https://www.ucpaas.com/index.html',
    'app_id' => '',
    'token' => '',
    'sid' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\YunzhixunGateway',
],
```

- [凯信通](http://www.kingtto.cn/Interface/program/499.html)

> 使用说明：template_content + template_params

```
'kaixintong' => [
    'sms_name' => '凯信通',
    'sms_url' => 'http://www.kingtto.cn',
    'account' => '',
    'password' => '',
    'userid' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\KaixintongGateway',
],
```

- [聚合数据](https://www.juhe.cn/docs/api/id/54)

> 使用说明：template_code + template_params

```
'juhe' => [
    'sms_name' => '聚合数据',
    'sms_url' => 'https://www.juhe.cn',
    'key' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\JuheGateway',
],
```

- [阿凡达数据](https://www.avatardata.cn/Docs/Api/fd475e40-7809-4be7-936c-5926dd41b0fe)

> 使用说明：template_code + template_params

```
'afanda' => [
    'sms_name' => '阿凡达数据',
    'sms_url' => 'https://www.avatardata.cn',
    'key' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\AfandaGateway',
],
```

- [网易云信](https://dev.yunxin.163.com/docs/product/%E7%9F%AD%E4%BF%A1/%E7%9F%AD%E4%BF%A1%E6%8E%A5%E5%85%A5%E7%A4%BA%E4%BE%8B)

> 使用说明：template_code + template_params

```
'wangyiyunxin' => [
    'sms_name' => '网易云信',
    'sms_url' => 'https://yunxin.163.com/sms',
    'app_key' => '',
    'app_secret' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\WangyiYunxinGateway',
],
```

- [容联云通讯](https://doc.yuntongxun.com/p/5a533de33b8496dd00dce07c)

> 使用说明：template_code + template_params

```
'rongliancloud' => [
    'sms_name' => '容联云通讯',
    'sms_url' => 'https://www.yuntongxun.com/',
    'app_key' => '',
    'app_secret' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\RonglianGateway',
],
```

- [优刻得](https://docs.ucloud.cn/api/usms-api/send_usms_message)

> 使用说明：template_code + template_params

```
'ucloud' => [
    'sms_name' => '优刻得',
    'sms_url' => 'https://www.ucloud.cn',
    'private_key' => '',
    'public_key' => '',
    'sig_content' => '',
    'project_id' => '',
    'driver' => 'Hongyukeji\\PhpSms\\Gateways\\RonglianCloudGateway',
],
```
