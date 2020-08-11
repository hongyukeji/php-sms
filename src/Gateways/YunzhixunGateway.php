<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 云之讯短信
 * @version v1.0
 * @see http://docs.ucpaas.com/doku.php
 *
 * Class YunzhixunGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class YunzhixunGateway extends Gateway
{
    const REQUEST_HOST = 'https://open.ucpaas.com';
    const REQUEST_SINGLE_URI = '/ol/sms/sendsms';
    const REQUEST_MANY_URI = '/ol/sms/sendsms_batch';
    const REQUEST_TIMEOUT = 0;

    protected $config;

    protected $sid;
    protected $token;
    protected $appid;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->sid = $config['sid'];
        $this->token = $config['token'];
        $this->appid = $config['appid'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'sid' => $this->sid,
            'token' => $this->token,
            'appid' => $this->appid,
            'mobile' => $mobile_number,
            'templateid' => $template_code,
            'param' => implode(',', $template_params),
        ];

        $headers = [];

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $request_url = self::REQUEST_HOST . self::REQUEST_MANY_URI;
            $form_params['mobile'] = implode(',', $mobile_number);
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

        if ($result['code'] == '0' || $result['code'] == '000000') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['message'], $result);
        }
    }

}
