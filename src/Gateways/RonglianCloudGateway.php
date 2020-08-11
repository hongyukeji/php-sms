<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 容联云通讯 短信
 * @version v1.0
 * @see https://doc.yuntongxun.com/p/5a533de33b8496dd00dce07c
 *
 * Class ExampleGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class RonglianCloudGateway extends Gateway
{
    const REQUEST_HOST = 'https://app.cloopen.com:8883';
    const REQUEST_TIMEOUT = 0;
    const STATUS_CODE_SUCCESS = '000000';

    protected $config;

    private $AccountSid;
    private $AccountToken;
    private $AppId;
    private $Batch;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->AccountSid = $config['account_sid'];
        $this->AccountToken = $config['account_token'];
        $this->AppId = $config['app_id'];

        $this->Batch = date("YmdHis");
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        //说明：主账号，登陆云通讯网站后，可在控制台首页看到开发者主账号ACCOUNT SID。
        $accountSid = $this->AccountSid;

        //说明：主账号Token，登陆云通讯网站后，可在控制台首页看到开发者主账号AUTH TOKEN。
        $accountToken = $this->AccountToken;

        //说明：请使用管理控制台中已创建应用的APPID。
        $appId = $this->AppId;

        $softVersion = '2013-12-26';
        $funcdes = 'TemplateSMS';
        $SigParameter = strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));

        //说明：REST API版本号保持不变。
        $request_url = self::REQUEST_HOST . "/{$softVersion}/Accounts/{$accountSid}/SMS/{$funcdes}?sig={$SigParameter}";

        $form_params = [
            'to' => $mobile_number,
            'templateId' => $template_code,
            'appId' => $appId,
            'datas' => array_values($template_params),
        ];

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json;charset=utf-8',
            'Content-Length' => '256',
            'Authorization' => base64_encode($this->AccountSid . ":" . $this->Batch),   // 账户Id + 冒号 + 时间戳
        ];

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $form_params['to'] = implode(',', $mobile_number);
        }

        try {
            $client = new Client();
            $response = $client->request('POST', $request_url, [
                'form_params' => $form_params,
                'headers' => $headers,
                'verify' => false,
                'timeout' => self::REQUEST_TIMEOUT,
            ]);
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($response);
        } catch (RequestException $e) {
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($e->getResponse());
        }

        if ($result['statusCode'] !== self::STATUS_CODE_SUCCESS || $result['statusCode'] !== 0) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['statusMsg'], $result);
        }
        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

}
