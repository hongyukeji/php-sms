<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 网易云信 短信
 * @version v1.0
 * @see https://dev.yunxin.163.com/docs/product/%E7%9F%AD%E4%BF%A1/%E7%9F%AD%E4%BF%A1%E6%8E%A5%E5%85%A5%E7%A4%BA%E4%BE%8B
 *
 * Class ExampleGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class WangyiYunxinGateway extends Gateway
{
    const REQUEST_TIMEOUT = 5000;
    const STATUS_CODE_SUCCESS = 200;

    protected $config;

    protected $AppKey;
    protected $AppSecret;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->AppKey = $config['app_key'];
        $this->AppSecret = $config['app_secret'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        if (isset($template_params['code'])) {
            // 验证码短信
            $request_url = 'https://api.netease.im/sms/sendcode.action';
            $form_params = [
                'mobile' => $mobile_number,
                'templateid' => $template_code,
                'authCode' => $template_params['code'],
                //'codeLen' => strlen($template_params['code']),
            ];
        } else {
            // 模板短信
            $request_url = 'https://api.netease.im/sms/sendtemplate.action';
            $form_params = [
                'templateid' => $template_code,
                'params' => json_encode(array_values($template_params)),
            ];
            // 手机号码格式处理
            if (is_array($mobile_number)) {
                $form_params['mobiles'] = json_encode($mobile_number);
            } else {
                $form_params['mobiles'] = json_encode(array($mobile_number));
            }
        }

        $headers = [
            'AppKey' => $this->AppKey,
            'Nonce' => md5(uniqid('php-sms')),
            'CurTime' => (string)time(),
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];

        $headers['CheckSum'] = sha1("{$this->AppSecret}{$headers['Nonce']}{$headers['CurTime']}");

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

        if ($result['code'] !== self::STATUS_CODE_SUCCESS) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['message'], $result);
        }
        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

}
