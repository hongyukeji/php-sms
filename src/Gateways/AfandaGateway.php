<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 阿凡达数据短信
 * @version v1.0
 * @see https://www.avatardata.cn/Docs/Api/fd475e40-7809-4be7-936c-5926dd41b0fe
 *
 * Class JuheGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class AfandaGateway extends Gateway
{
    const REQUEST_HOST = 'http://v1.avatardata.cn';
    const REQUEST_SINGLE_URI = '/Sms/Send';
    const REQUEST_MANY_URI = '/Sms/Send';

    const STATUS_CODE_SUCCESS = 0;

    protected $config;

    protected $key;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->key = $config['key'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'key' => $this->key,
            'mobile' => $mobile_number,
            'templateId' => $template_code,
            'param' => implode(',', $template_params),
            'dtype' => 'json',
        ];

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $request_url = self::REQUEST_HOST . self::REQUEST_MANY_URI;
            $form_params['mobile'] = implode(',', $mobile_number);
        }

        try {
            $client = new Client();
            $response = $client->request('POST', $request_url, [
                'form_params' => $form_params,
                'verify' => false,
            ]);
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($response);
        } catch (RequestException $e) {
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($e->getResponse());
        }

        if ($result['error_code'] !== self::STATUS_CODE_SUCCESS) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['reason'], $result);
        }
        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

}
