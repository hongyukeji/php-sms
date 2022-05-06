<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * 短信宝短信
 * @version: v1.0
 * @see http://www.smsbao.com/openapi/55.html
 *
 * Class DuanxinbaoGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class DuanxinbaoGateway extends Gateway
{
    // 国内短信API
    protected $smsapi = "http://api.smsbao.com/";
    // 国际短信API
    protected $wsmsapi = "https://api.smsbao.com/wsms/";
    protected $user;
    protected $pass;
    protected $signName;
    protected $statusStr = array(
        "0" => "短信发送成功",
        "-1" => "参数不全",
        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30" => "密码错误",
        "40" => "账号不存在",
        "41" => "余额不足",
        "42" => "帐户已过期",
        "43" => "IP地址限制",
        "50" => "内容含有敏感词"
    );

    public function __construct($config)
    {
        parent::__construct($config);
        $this->user = $config['user'];  // 短信平台帐号
        $this->pass = $config['pass'];  // 短信平台密码
        $this->signName = $config['signName'];  // 短信签名
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        if (is_array($mobile_number)) {
            $result = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            $smsapi = $this->smsapi;
            $user = $this->user; //短信平台帐号
            $pass = md5($this->pass); //短信平台密码
            $signName = $this->signName; //短信签名
            $content = $signName . vsprintf($template_code, $template_params);//要发送的短信内容

            $phone = $mobile_number;//要发送短信的手机号码
            $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
            $result = file_get_contents($sendurl);
        }

        if ($result == '0') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $this->statusStr[$result]);
        }
    }

    public function batchSend($mobile_number, $template_code, $template_params)
    {
        $smsapi = $this->smsapi;
        $user = $this->user; //短信平台帐号
        $pass = md5($this->pass); //短信平台密码
        $signName = $this->signName; //短信签名
        $content = $signName . vsprintf($template_code, $template_params);//要发送的短信内容

        foreach ($mobile_number as $key => $value) {
            $phone = $value;//要发送短信的手机号码
            $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
            $result = file_get_contents($sendurl);
        }

        return $result;
    }

    public function sendInternationalSms($mobile_number, $template_code, $template_params)
    {
        $this->smsapi = $this->wsmsapi;

        // 接口说明 https://www.smsbao.com/openapi/299.html
        // 接收的手机号;群发时多个手机号以逗号分隔，一次不要超过99个号码
        //注：国际号码需包含国际地区前缀号码 格式必须是"+"号开头("+"号需要urlencode处理
        //如：urlencode("+60901234567")否则会出现格式错误)

        // 处理手机号 手机号判断是否存在 + 加号
        if (is_array($mobile_number)) {
            foreach ($mobile_number as $key => $val) {
                $mobile_number[$key] = $this->handleMobileFormat($val);
            }
        } else {
            $mobile_number = $this->handleMobileFormat($mobile_number);
        }

        return $this->send($mobile_number, $template_code, $template_params);
    }

    public function batchSendInternationalSms($mobile_number, $template_code, $template_params)
    {
        $this->smsapi = $this->wsmsapi;
        // 手机号处理
        foreach ($mobile_number as $key => $val) {
            $mobile_number[$key] = $this->handleMobileFormat($val);
        }
        return $this->batchSend($mobile_number, $template_code, $template_params);
    }

    /**
     * 处理手机号格式
     *
     * @param $mobile_number
     * @return string
     */
    public function handleMobileFormat($mobile_number)
    {
        $symbol = '+';
        if (substr($mobile_number, 0, strlen($symbol)) === $symbol) {
        } else {
            $mobile_number = $symbol . $mobile_number;
        }
        $mobile_number = urlencode($mobile_number);
        return $mobile_number;
    }
}
