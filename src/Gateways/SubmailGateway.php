<?php

namespace Hongyukeji\PhpSms\Gateways;

require_once dirname(__FILE__) . '/../Library/Submail/SUBMAILAutoload.php';

use MESSAGEXsend;
use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * 赛邮云通信短信
 * @version: v1.0
 * @see https://www.mysubmail.com/chs/documents/developer/index
 *
 * Class SubmailGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class SubmailGateway extends Gateway
{
    protected $message_configs;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->message_configs['appid'] = $config['appid'];
        $this->message_configs['appkey'] = $config['appkey'];
        $this->message_configs['sign_type'] = 'normal';
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        if (is_array($mobile_number)) {
            $result = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            $submail = new MESSAGEXsend($this->message_configs);
            $submail->setTo($mobile_number);
            $submail->SetProject($template_code);
            foreach ($template_params as $key => $value) {
                $submail->AddVar($key, $value);
            }
            $result = $submail->xsend();
        }
        if ($result['status'] == 'success') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, '错误代码：' . $result['code'] . ' 描述：' . $result['msg'], $result);
        }
    }

    public function batchSend($mobile_number, $template_code, $template_params)
    {
        $submail = new MESSAGEXsend($this->message_configs);
        foreach ($mobile_number as $k => $v) {
            $submail->setTo($v);
            $submail->SetProject($template_code);
            foreach ($template_params as $key => $value) {
                $submail->AddVar($key, $value);
            }
            $xsend = $submail->xsend();
        }
        return $xsend;
    }
}
