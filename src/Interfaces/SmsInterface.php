<?php

namespace Hongyukeji\PhpSms\Interfaces;

interface SmsInterface
{
    /**
     * 初始化操作时传入当前短信服务商配置项
     *
     * SmsInterface constructor.
     * @param $config
     */
    public function __construct($config);

    /**
     * 发送短信
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return mixed
     */
    public function send($mobile_number, $template_code, $template_params);

    /**
     * 批量发送短信
     *
     * @param $mobile_number
     * @param $template_code
     * @param $template_params
     * @return mixed
     */
    //public function batchSend($mobile_number, $template_code, $template_params);
}
