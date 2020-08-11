<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Library\Qcloud\SmsMultiSender;
use Hongyukeji\PhpSms\Library\Qcloud\SmsSenderUtil;
use Hongyukeji\PhpSms\Library\Qcloud\SmsSingleSender;
use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * 腾讯云短信
 * @version: v1.0
 * @see https://cloud.tencent.com/document/product/382/9557
 *
 * Class QcloudGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class QcloudGateway extends Gateway
{
    protected $appid;
    protected $appkey;
    protected $smsSign;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->appid = $config['appid'];
        $this->appkey = $config['appkey'];
        $this->smsSign = $config['smsSign'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        if (is_array($mobile_number)) {
            $result = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            // 短信应用SDK AppID
            $appid = $this->appid; // 1400开头
            // 短信应用SDK AppKey
            $appkey = $this->appkey;
            // 需要发送短信的手机号码
            $mobile = $mobile_number;
            // 短信模板ID，需要在短信应用中申请
            $templateId = $template_code;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
            // 签名
            $smsSign = $this->smsSign; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
            // 指定模板ID单发短信
            try {
                $ssender = new SmsSingleSender($appid, $appkey);
                $params = $template_params;
                $result = $ssender->sendWithParam("86", $mobile, $templateId, $params, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
                $rsp = json_decode($result);
                //echo $result;
            } catch (\Exception $e) {
                echo var_dump($e);
            }
        }
        $response = json_decode($result, true);
        if ($response['result'] == '0') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $response['errmsg'], $response);
        }
    }

    public function batchSend($mobile_number, $template_code, $template_params)
    {
        // 短信应用SDK AppID
        $appid = $this->appid; // 1400开头
        // 短信应用SDK AppKey
        $appkey = $this->appkey;
        // 需要发送短信的手机号码
        $mobile = $mobile_number;
        // 短信模板ID，需要在短信应用中申请
        $templateId = $template_code;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
        // 签名
        $smsSign = $this->smsSign; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
        try {
            $msender = new SmsMultiSender($appid, $appkey);
            $params = $template_params;
            $result = $msender->sendWithParam("86", $mobile,
                $templateId, $params, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result);
            //echo $result;
        } catch (\Exception $e) {
            echo var_dump($e);
        }
        return $result;
    }
}
