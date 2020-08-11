<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 优刻得 短信
 * @version v1.0
 * @see https://docs.ucloud.cn/api/usms-api/send_usms_message
 *
 * Class ExampleGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class UcloudGateway extends Gateway
{
    const REQUEST_HOST = 'https://api.ucloud.cn';
    const REQUEST_SINGLE_URI = '/?Action=SendUSMSMessage';
    const REQUEST_MANY_URI = '/?Action=SendUSMSMessage';
    const REQUEST_TIMEOUT = 0;
    const STATUS_CODE_SUCCESS = 0;

    protected $config;

    protected $ProjectId;
    protected $PrivateKey;
    protected $PublicKey;
    protected $SigContent;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->ProjectId = isset($config['project_id']) ? $config['project_id'] : null;
        $this->PrivateKey = $config['private_key'];
        $this->PublicKey = $config['public_key'];
        $this->SigContent = $config['sig_content'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'SigContent' => $this->SigContent,
            'TemplateId' => $template_code,
        ];
        if (!empty($this->ProjectId)) {
            $form_params['ProjectId'] = $this->ProjectId;
        }

        $headers = [
            'Action' => 'SendUSMSMessage',
            'PublicKey' => $this->PublicKey,
        ];

        $headers['Signature'] = $this->verify_ac($this->PrivateKey, $headers);

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $request_url = self::REQUEST_HOST . self::REQUEST_MANY_URI;
            foreach ($mobile_number as $key => $value) {
                $form_params["PhoneNumbers.{$key}"] = $value;
                $form_params["TemplateParam.{$key}"] = $template_params;
            }
        } else {
            $form_params['PhoneNumbers.0'] = $mobile_number;
            $form_params['TemplateParam.0'] = $template_params;
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

        if ($result['RetCode'] !== self::STATUS_CODE_SUCCESS) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['Message'], $result);
        }
        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

    public function verify_ac($private_key, $params)
    {
        ksort($params);

        $params_data = "";

        foreach ($params as $key => $value) {
            $params_data .= $key;
            $params_data .= $value;
        }

        $params_data .= $private_key;
        return sha1($params_data);
    }
}
