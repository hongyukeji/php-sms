<?php

namespace Hongyukeji\PhpSms\Gateways;

require_once dirname(__FILE__) . '/../Library/Sendcloud/SendCloudSMS.php';
require_once dirname(__FILE__) . '/../Library/Sendcloud/util/SMS.php';

use SendCloudSMS;
use SmsMsg;
use Hongyukeji\PhpSms\Gateways\Gateway;

/**
 * SendCloud短信
 * @version: v1.0
 * @see https://www.sendcloud.net/doc/sms/
 *
 * Class SendCloudGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class SendCloudGateway extends Gateway
{
    protected $sms_user;
    protected $sms_key;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->sms_user = $config['sms_user'];
        $this->sms_key = $config['sms_key'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        if (is_array($mobile_number)) {
            $result = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            $SMS_USER = $this->sms_user;
            $SMS_KEY = $this->sms_key;
            $smsTemplateId = $template_code;
            $sendSms = new SendCloudSMS($SMS_USER, $SMS_KEY);
            $smsMsg = new SmsMsg();
            $smsMsg->addPhoneList(array($mobile_number));
            foreach ($template_params as $key => $value) {
                $smsMsg->addVars($key, $value);
            }
            $smsMsg->setTemplateId($smsTemplateId);
            $smsMsg->setTimestamp(time());
            $response = $sendSms->send($smsMsg);
            $result = $response->body();
        }
        $response = json_decode($result, true);
        if ($response['status'] == '200') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $response['message'], $response);
        }
    }

    public function batchSend($mobile_number, $template_code, $template_params)
    {
        $SMS_USER = $this->sms_user;
        $SMS_KEY = $this->sms_key;
        $smsTemplateId = $template_code;
        $sendSms = new SendCloudSMS($SMS_USER, $SMS_KEY);
        $smsMsg = new SmsMsg();
        $smsMsg->addPhoneList($mobile_number);
        foreach ($template_params as $key => $value) {
            $smsMsg->addVars($key, $value);
        }
        $smsMsg->setTemplateId($smsTemplateId);
        $smsMsg->setTimestamp(time());
        $response = $sendSms->send($smsMsg);
        return $response->body();
    }
}
