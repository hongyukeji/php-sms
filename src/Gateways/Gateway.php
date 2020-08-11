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
     * @inheritDoc
     */
    public function send($mobile_number, $template_code, $template_params)
    {
        // TODO: Implement send() method.
        if (is_array($mobile_number)) {
            $response = $this->batchSend($mobile_number, $template_code, $template_params);
        }else{
            $response = [];
        }

        if ($response['status'] === '200') {
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
}
