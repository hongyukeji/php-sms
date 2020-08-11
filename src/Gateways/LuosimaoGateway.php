<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * 螺丝帽
 * @version v1.0
 * @see https://luosimao.com/docs/api
 *
 * Class LuosimaoGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class LuosimaoGateway extends Gateway
{
    protected $config;

    protected $api_key;

    /**
     * @inheritDoc
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->api_key = $config['api_key'];
    }

    /**
     * @inheritDoc
     */
    public function send($mobile_number, $template_code, $template_params)
    {
        if (is_array($mobile_number)) {
            $response = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'api:key-' . $this->api_key);

            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile_number, 'message' => vsprintf($template_code, $template_params)));

            $res = curl_exec($ch);
            curl_close($ch);
            //$res  = curl_error( $ch );
            //var_dump($res);
            $response = $res;
        }

        if ($response['error'] === '0') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $response['msg'], $response);
        }
    }

    /**
     * @inheritDoc
     */
    public function batchSend($mobile_number, $template_code, $template_params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send_batch.json");

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:key-' . $this->api_key);

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => implode(',', $mobile_number), 'message' => vsprintf($template_code, $template_params)));

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
