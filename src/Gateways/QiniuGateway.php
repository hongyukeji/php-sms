<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 七牛云短信
 * @version v1.0
 * @see https://developer.qiniu.com/sms/api/5897/sms-api-send-message
 *
 * Class QiniuGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class QiniuGateway extends Gateway
{
    const REQUEST_HOST = 'https://sms.qiniuapi.com';
    const REQUEST_SINGLE_URI = '/v1/message/single';
    const REQUEST_MANY_URI = '/v1/message';
    const REQUEST_TIMEOUT = 0;

    protected $config;
    protected $access_key;
    protected $secret_key;
    protected $sms_sign;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->access_key = $config['access_key'];
        $this->secret_key = $config['secret_key'];
        $this->sms_sign = $config['sms_sign'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'template_id' => $template_code,
            'mobiles' => $mobile_number,
            'parameters' => $template_params,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->generateSign($request_url, 'POST', json_encode($form_params), 'application/json')
        ];

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $request_url = self::REQUEST_HOST . self::REQUEST_MANY_URI;
            $form_params['mobiles'] = $mobile_number;
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

        if (!empty($result['job_id']) || !empty($result['message_id'])) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['message'], $result);
        }
    }

    /**
     * Build endpoint url.
     *
     * @param string $url
     * @param string $method
     * @param string $body
     * @param string $contentType
     *
     * @return string
     */
    protected function generateSign($url, $method, $body = null, $contentType = null)
    {
        $urlItems = parse_url($url);
        $host = $urlItems['host'];
        if (isset($urlItems['port'])) {
            $port = $urlItems['port'];
        } else {
            $port = '';
        }
        $path = $urlItems['path'];
        if (isset($urlItems['query'])) {
            $query = $urlItems['query'];
        } else {
            $query = '';
        }
        //write request uri
        $toSignStr = $method . ' ' . $path;
        if (!empty($query)) {
            $toSignStr .= '?' . $query;
        }
        //write host and port
        $toSignStr .= "\nHost: " . $host;
        if (!empty($port)) {
            $toSignStr .= ':' . $port;
        }
        //write content type
        if (!empty($contentType)) {
            $toSignStr .= "\nContent-Type: " . $contentType;
        }
        $toSignStr .= "\n\n";
        //write body
        if (!empty($body)) {
            $toSignStr .= $body;
        }

        $hmac = hash_hmac('sha1', $toSignStr, $this->secret_key, true);

        return 'Qiniu ' . $this->access_key . ':' . $this->base64UrlSafeEncode($hmac);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function base64UrlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');

        return str_replace($find, $replace, base64_encode($data));
    }
}
