<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;

class HuaxinGateway extends Gateway
{
    const url = "https://dx.ipyy.net/sms.aspx";

    protected $config;
    protected $account;
    protected $password;

    /**
     * @inheritDoc
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->account = $config['account'];
        $this->password = $config['password'];
    }

    /**
     * @inheritDoc
     */
    public function send($mobile_number, $template_code, $template_params)
    {
        $account = $this->account;  //改为实际账户名
        $password = $this->password;    //改为实际短信发送密码
        if (is_array($mobile_number)) {
            $mobiles = implode(',', $mobile_number);  //目标手机号码，多个用半角“,”分隔
        } else {
            $mobiles = $mobile_number;
        }
        $extno = "";
        $content = vsprintf($template_code, $template_params);
        $sendtime = "";

        $result = self::result_send($account, $password, $mobiles, $extno, $content, $sendtime);

        $xml = simplexml_load_string($result);
        //echo "返回信息提示：" . $xml->message . "\n";
        //echo "返回状态为：" . $xml->returnstatus . "\n";
        //echo "返回信息：" . $xml->message . "\n";
        //echo "返回余额：" . $xml->remainpoint . "\n";
        //echo "返回本次任务ID：" . $xml->taskID . "\n";
        //echo "返回成功短信数：" . $xml->successCounts . "\n";

        $response = [
            'message' => $xml->message,
            'returnstatus' => $xml->returnstatus,
            'remainpoint' => $xml->remainpoint,
            'taskID' => $xml->taskID,
            'successCounts' => $xml->successCounts,
        ];
        if ($response['returnstatus'] === 'Success') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $response['message'], $response);
        }
    }

    /**
     * @inheritDoc
     */
    public function batchSend($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement batchSend() method.
    }

    public static function result_send($account, $password, $mobiles, $extno, $content, $sendtime)
    {
        $body = array(
            'action' => 'send',
            'userid' => '',
            'account' => $account,
            'password' => $password,
            'mobile' => $mobiles,
            'extno' => $extno,
            'content' => $content,
            'sendtime' => $sendtime
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
