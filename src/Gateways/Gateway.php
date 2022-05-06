<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Interfaces\SmsInterface;

class Gateway implements SmsInterface
{
    protected $config;

    /**
     * @inheritDoc
     */
    public function __construct($config)
    {
        //parent::__construct($config);
        $this->config = $config;
    }

    /**
     * 发送短信
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return array|mixed
     */
    public function send($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement send() method.
        if (is_array($mobile_number)) {
            $response = $this->batchSend($mobile_number, $template_code, $template_params);
        } else {
            $response = array();
        }

        if ($response['status'] === '200') {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
        } else {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $response['message'], $response);
        }
    }

    /**
     * 批量发送短信
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return void
     */
    public function batchSend($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement batchSend() method.
    }

    /**
     * 发送国际短信
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return void
     */
    public function sendInternationalSms($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement sendInternationalSms() method.
    }

    /**
     * 批量发送国际短信
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return void
     */
    public function batchSendInternationalSms($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement batchSendInternationalSms() method.
    }
}
