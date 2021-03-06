<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * 互亿无线短信
 * @version: v1.0
 * @see http://www.ihuyi.com/demo/sms/php.html
 *
 * Class IhuyiGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class IhuyiGateway extends Gateway
{
    protected $target = "http://106.ihuyi.com/webservice/sms.php?method=Submit";
    protected $apiid;
    protected $apikey;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->apiid = $config['apiid'];
        $this->apikey = $config['apikey'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        if (is_array($mobile_number)) {
            $gets = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            //短信接口地址
            $target = $this->target;
            //获取手机号
            $mobile = $mobile_number;
            // 短信内容格式处理
            $content = vsprintf($template_code, $template_params);
            $post_data = "account=" . $this->apiid . "&password=" . $this->apikey . "&mobile=" . $mobile . "&content=" . rawurlencode($content);
            //查看用户名 登录用户中心->验证码通知短信>产品总览->API接口信息->APIID
            //查看密码 登录用户中心->验证码通知短信>产品总览->API接口信息->APIKEY
            $gets = $this->xml_to_array($this->Post($post_data, $target));
        }

        if ($gets['SubmitResult']['code'] == '2') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $gets['SubmitResult']['msg']);
        }
    }

    public function batchSend($mobile_number, $template_code, $template_params)
    {
        $target = $this->target;
        $content = vsprintf($template_code, $template_params);
        foreach ($mobile_number as $key => $value) {
            $mobile = $value;
            $post_data = "account=" . $this->apiid . "&password=" . $this->apikey . "&mobile=" . $mobile . "&content=" . rawurlencode($content);
            $gets = $this->xml_to_array($this->Post($post_data, $target));
        }
        return $gets;
    }

    //请求数据到短信接口，检查环境是否 开启 curl init。
    public function Post($curlPost, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    //将 xml数据转换为数组格式。
    public function xml_to_array($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
}
