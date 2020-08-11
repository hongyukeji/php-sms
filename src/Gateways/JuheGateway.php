<?php

namespace Hongyukeji\PhpSms\Gateways;

use Hongyukeji\PhpSms\Gateways\Gateway;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 聚合数据短信
 * @version v1.0
 * @see https://www.juhe.cn/docs/api/id/54
 *
 * Class JuheGateway
 * @package Hongyukeji\PhpSms\Gateways
 */
class JuheGateway extends Gateway
{
    const REQUEST_HOST = 'http://v.juhe.cn';
    const REQUEST_SINGLE_URI = '/sms/send';
    const REQUEST_MANY_URI = '/sms/send';
    const STATUS_CODE_SUCCESS = 0;

    protected $config;

    protected $key;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->key = $config['key'];
    }

    public function send($mobile_number, $template_code, $template_params)
    {
        $request_url = self::REQUEST_HOST . self::REQUEST_SINGLE_URI;

        $form_params = [
            'key' => $this->key,
            'mobile' => $mobile_number,
            'tpl_id' => $template_code,
            'tpl_value' => $this->formatTemplateVars($template_params),
            'dtype' => 'json',
        ];

        // 多个手机号码，群发处理
        if (is_array($mobile_number)) {
            $request_url = self::REQUEST_HOST . self::REQUEST_MANY_URI;
            $form_params['mobile'] = implode(',', $mobile_number);
            //throw new \Exception("短信服务商不支持批量发送短信！");
            //$form_params['TemplateContent'] = vsprintf($template_code, $template_params);
        }

        try {
            $client = new Client();
            $response = $client->request('POST', $request_url, [
                'form_params' => $form_params,
                'verify' => false,
            ]);
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($response);
        } catch (RequestException $e) {
            $result = \Hongyukeji\PhpSms\Sms::formatResponse($e->getResponse());
        }

        if ($result['error_code'] !== self::STATUS_CODE_SUCCESS) {
            return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_FAIL, $result['reason'], $result);
        }
        return \Hongyukeji\PhpSms\Sms::result(\Hongyukeji\PhpSms\Constants\SmsConstant::SMS_STATUS_SUCCESS);
    }

    /**
     * 格式化模板变量
     *
     * @param array $vars
     * @return string
     */
    protected function formatTemplateVars(array $vars)
    {
        $formatted = [];
        foreach ($vars as $key => $value) {
            $formatted[sprintf('#%s#', trim($key, '#'))] = $value;
        }
        return http_build_query($formatted);
    }
}
