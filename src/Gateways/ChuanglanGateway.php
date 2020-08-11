<?php

namespace Hongyukeji\PhpSms\Gateways;

require_once dirname(__FILE__) . '/../Library/Chuanglan/ChuanglanSmsApi.php';

/**
 * 创蓝短信
 * @version: v1.0
 * @see https://zz.253.com/api_doc
 *
 * Class ChuanglanGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class ChuanglanGateway extends Gateway
{
    protected $config;
    protected $clapi;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->config = $config;
        $this->clapi = new \ChuanglanSmsApi($config);
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        // 判断是否需要群发
        if (is_array($mobile_number)) {
            $result = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            $params = array_values($template_params);
            array_unshift($params, $mobile_number);
            $msg = $template_code;
            if (isset($this->config['sms_sign'])) {
                $msg = '【' . $this->config['sms_sign'] . '】' . $msg;
            }
            $params = implode(',', $params);
            $result = $this->clapi->sendVariableSMS($msg, $params);
        }

        // {"code":"0","failNum":"0","successNum":"1","msgId":"19101318284626959","time":"20191013182846","errorMsg":""}
        if (!is_null(json_decode($result))) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] == '0') {
                return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
            } else {
                return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, isset($output['errorMsg']) ? $output['errorMsg'] : (isset($output['message']) ? $output['message'] : ''), $result);
            }
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, '短信发送失败！', $result);
        }
    }

    public function batchSend($mobile_number, $template_code, $template_params)
    {
        $msg = $template_code;
        $param_array = [];
        foreach ($mobile_number as $phoneNumber) {
            $param_array[] = $phoneNumber . ',' . implode(',', array_values($template_params));
        }
        $params = implode(';', $param_array);
        $result = $this->clapi->sendVariableSMS($msg, $params);
        return $result;
    }
}
