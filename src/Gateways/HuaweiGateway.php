<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 华为云短信
 * @version v1.0
 * @see https://support.huaweicloud.com/api-msgsms/sms_05_0000.html
 *
 * Class HuaweiGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class HuaweiGateway extends Gateway
{
    protected $config;
    protected $app_key;
    protected $app_secret;
    protected $sms_sign;

    /**
     * @inheritDoc
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->app_key = $config['app_key'];
        $this->app_secret = $config['app_secret'];
        $this->sms_sign = $config['sms_sign'];
    }

    /**
     * @inheritDoc
     */
    public function send($mobile_number, $template_code, $template_params)
    {
        //必填,请参考"开发准备"获取如下数据,替换为实际值
        $url = 'https://rtcsms.cn-north-1.myhuaweicloud.com:10743/sms/batchSendSms/v1'; //APP接入地址+接口访问URI
        $APP_KEY = $this->app_key; //APP_Key
        $APP_SECRET = $this->app_secret; //APP_Secret
        $sender = 'csms12345678'; //国内短信签名通道号或国际/港澳台短信通道号
        $TEMPLATE_ID = $template_code; //模板ID

        //条件必填,国内短信关注,当templateId指定的模板类型为通用模板时生效且必填,必须是已审核通过的,与模板类型一致的签名名称
        //国际/港澳台短信不用关注该参数
        $signature = $this->sms_sign; //签名名称

        //必填,全局号码格式(包含国家码),示例:+8615123456789,多个号码之间用英文逗号分隔
        if (is_array($mobile_number)) {
            $mobile_numbers = array_map(function ($item) {
                return '+86' . $item;
            }, $mobile_number);
            $receiver = implode(',', $mobile_numbers);
        } else {
            $receiver = "+86{$mobile_number}"; //短信接收人号码
        }

        //选填,短信状态报告接收地址,推荐使用域名,为空或者不填表示不接收状态报告
        $statusCallback = '';

        /**
         * 选填,使用无变量模板时请赋空值 $TEMPLATE_PARAS = '';
         * 单变量模板示例:模板内容为"您的验证码是${1}"时,$TEMPLATE_PARAS可填写为 '["369751"]'
         * 双变量模板示例:模板内容为"您有${1}件快递请到${2}领取"时,$TEMPLATE_PARAS可填写为'["3","人民公园正门"]'
         * 模板中的每个变量都必须赋值，且取值不能为空
         * 查看更多模板格式规范:产品介绍>模板和变量规范
         * @var string $TEMPLATE_PARAS
         */
        //$TEMPLATE_PARAS = '["369751"]'; //模板变量，此处以单变量验证码短信为例，请客户自行生成6位验证码，并定义为字符串类型，以杜绝首位0丢失的问题（例如：002569变成了2569）。
        $TEMPLATE_PARAS = json_encode($template_params);

        try {
            $client = new Client();
            $response = $client->request('POST', $url, [
                'form_params' => [
                    'from' => $sender,
                    'to' => $receiver,
                    'templateId' => $TEMPLATE_ID,
                    'templateParas' => $TEMPLATE_PARAS,
                    'statusCallback' => $statusCallback,
                    //'signature' => $signature //使用国内短信通用模板时,必须填写签名名称
                ],
                'headers' => [
                    'Authorization' => 'WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
                    'X-WSSE' => $this->buildWsseHeader($APP_KEY, $APP_SECRET)
                ],
                'verify' => false //为防止因HTTPS证书认证失败造成API调用失败，需要先忽略证书信任问题
            ]);
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($response);
        } catch (RequestException $e) {
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($e->getResponse());
        }

        if ($result['code'] !== '000000') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['description'], $result);
        }

        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

    /**
     * @inheritDoc
     */
    public function batchSend($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement batchSend() method.
    }

    /**
     * 构造X-WSSE参数值
     * @param string $appKey
     * @param string $appSecret
     * @return string
     */
    public function buildWsseHeader(string $appKey, string $appSecret)
    {
        $now = date('Y-m-d\TH:i:s\Z'); //Created
        $nonce = uniqid(); //Nonce
        $base64 = base64_encode(hash('sha256', ($nonce . $now . $appSecret))); //PasswordDigest
        return sprintf("UsernameToken Username=\"%s\",PasswordDigest=\"%s\",Nonce=\"%s\",Created=\"%s\"",
            $appKey, $base64, $nonce, $now);
    }

}
