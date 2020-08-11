<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * 云片网短信
 * @version: v1.0
 * @see https://www.yunpian.com/doc/zh_CN/introduction/demos/php.html
 *
 * Class YunpianGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class YunpianGateway extends Gateway
{
    protected $config;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->config = $config;
    }

    /**
     * 云片网开发文档
     * php DEMO https://www.yunpian.com/doc/zh_CN/introduction/demos/php.html
     * 返回码总体说明 https://www.yunpian.com/doc/zh_CN/returnValue/list.html
     * 返回值示例 https://www.yunpian.com/doc/zh_CN/returnValue/example.html
     * 常见的返回码 https://www.yunpian.com/doc/zh_CN/returnValue/common.html
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return mixed
     */
    public function send($mobile_number, $template_code, $template_params)
    {
        $apikey = $this->config['apikey'];
        $ch = curl_init();
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
            'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 格式化手机号
        if (!empty($mobile_number) && is_array($mobile_number)) {
            $mobile_number = $this->yunPianSwitchMobile($mobile_number);
        }
        // 格式化参数
        $template_params = $this->yunPianSwitchTplValue($template_params);
        // 发送短信
        $data = array('tpl_id' => $template_code, 'tpl_value' => $template_params, 'apikey' => $apikey, 'mobile' => $mobile_number);
        // 判断是否群发
        if (is_array($mobile_number)) {
            $json_data = $this->tpl_sends($ch, $data);
        } else {
            $json_data = $this->tpl_send($ch, $data);
        }
        $array = json_decode($json_data, true);
        curl_close($ch);
        $response = $array;
        if ($response['code'] == '0') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $response['msg'], $response);
        }
    }

    /**
     * 云片短信处理手机号
     *
     * @param $array
     * @return null|string
     */
    public function yunPianSwitchMobile($array)
    {
        $string = array();

        if ($array && is_array($array)) {
            foreach ($array as $key => $value) {
                $string[] = $value;
            }
        } else {
            return null;
        }

        return implode(',', $string);
    }

    /**
     * 云片短信处理模板值
     *
     * @param $array
     * @return null|string
     */
    public function yunPianSwitchTplValue($array)
    {
        $string = array();

        if ($array && is_array($array)) {
            foreach ($array as $key => $value) {
                $string[] = '#' . $key . '#=' . $value;
            }
        } else {
            return null;
        }

        return implode('&', $string);
    }

    public function get_user($ch, $apikey)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/user/get.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result, $error);
        return $result;
    }

    public function request_send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'http://sms.yunpian.com/v2/sms/single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result, $error);
        return $result;
    }

    public function tpl_send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL,
            'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result, $error);
        return $result;
    }

    public function tpl_sends($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL,
            'https://sms.yunpian.com/v2/sms/tpl_batch_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result, $error);
        return $result;
    }

    public function voice_send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'http://voice.yunpian.com/v2/voice/send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result, $error);
        return $result;
    }

    public function notify_send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://voice.yunpian.com/v2/voice/tpl_notify.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $this->checkErr($result, $error);
        return $result;
    }

    public function checkErr($result, $error)
    {
        if ($result === false) {
            echo 'Curl error: ' . $error;
        } else {
            //echo '操作完成没有任何错误';
        }
    }
}
