<?php

namespace Hongyukeji\PhpSms\Constants;

class SmsConstant
{
    public const SMS_STATUS_SUCCESS = 0;
    public const SMS_STATUS_FAIL = 1;

    public const SMS_STATUS = [
        self::SMS_STATUS_SUCCESS => '短信发送成功',
        self::SMS_STATUS_FAIL => '短信发送失败',
    ];
}
