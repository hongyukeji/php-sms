<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 凯信通
 * @version v1.0
 * @see http://www.kingtto.cn/Interface/program/499.html
 *
 * Class KaixintongGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class KaixintongGateway extends Gateway
{
    const REQUEST_HOST = 'http://sms.kingtto.com:9999';
    const REQUEST_SINGLE_URI = '/sms.aspx';
    const REQUEST_MANY_URI = '/sms.aspx';
    const REQUEST_TIMEOUT = 0;
    const STATUS_CODE_SUCCESS = "success";

    protected $config;

    protected $account;
    protected $password;
    protected $userid;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->account = $config['account'];
        $this->password = $config['password'];
        $this->userid = $config['userid'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'account' => $this->account,
            'password' => $this->password,
            'userid' => $this->userid,
            'action' => 'send',
            'mobile' => $mobile_number,
            'content' => urlencode(vsprintf($template_code, $template_params)),
        ];

        $headers = [
            //'Content-Type' => 'application/json',
            //'Authorization' => '<ExampleToken>',
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
                'headers' => $headers,
                'verify' => false,
                'timeout' => self::REQUEST_TIMEOUT,
            ]);
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($response);
        } catch (RequestException $e) {
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($e->getResponse());
        }

        if (strtolower($result['returnstatus']) !== self::STATUS_CODE_SUCCESS) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['message'], $result);
        }

        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

}
