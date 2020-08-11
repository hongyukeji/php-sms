<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Example 短信
 * @version v1.0
 * @see https://help.example.com/docs
 *
 * Class ExampleGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class ExampleGateway extends Gateway
{
    const REQUEST_HOST = 'sms.example.com';
    const REQUEST_SINGLE_URI = '/v1/message/single';
    const REQUEST_MANY_URI = '/v1/message';
    const REQUEST_TIMEOUT = 0;
    const STATUS_CODE_SUCCESS = 0;

    protected $config;

    protected $app_key;
    protected $app_secret;
    protected $sms_sign;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->app_key = $config['app_key'];
        $this->app_secret = $config['app_secret'];
        $this->sms_sign = $config['sms_sign'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'AppKey' => $this->app_key,
            'AppSecret' => $this->app_secret,
            'PhoneNumbers' => $mobile_number,
            'TemplateCode' => $template_code,
            'TemplateParam' => $template_params,
        ];

        $headers = [
            //'Content-Type' => 'application/json',
            //'Authorization' => '<ExampleToken>',
        ];

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $request_url = self::REQUEST_HOST . self::REQUEST_MANY_URI;
            $form_params['PhoneNumbers'] = implode(',', $mobile_number);

            //throw new \Exception("短信服务商不支持批量发送短信！");
            //$form_params['TemplateContent'] = vsprintf($template_code, $template_params);
        }

        try {
            $client = new Client();
            $response = $client->request('POST', $request_url, [
                'form_params' => $form_params,
                'headers' => $headers,
                'verify' => false,
                'timeout' => self::REQUEST_TIMEOUT,
            ]);
            //echo Psr7\str($response);
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($response);
        } catch (RequestException $e) {
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($e->getResponse());
            /*
            echo $e;
            echo Psr7\str($e->getRequest()), "\n";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
            */
        }

        if ($result['code'] !== self::STATUS_CODE_SUCCESS) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['message'], $result);
        }
        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

}
