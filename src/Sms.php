<?php

namespace Hongyukeji\PhpSms;

use Hongyukeji\PhpSms\Constants\SmsConstant;
use Psr\Http\Message\ResponseInterface;

class Sms
{
    protected $config;

    /**
     * 初始化并传入短信所有配置参数
     *
     * Sms constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 发送短信
     *
     * @param string|array $mobile_number
     * @param string $template_code
     * @param array|null $template_params
     * @param string|null $gateway
     * @param array|null $config
     * @return array
     */
    public function send($mobile_number, string $template_code, array $template_params = [], string $gateway = null, array $config = null)
    {
        // 如果$gateway不存在，则调用默认短信服务商
        if (empty($gateway)) {
            $gateway = isset($this->config['default']) ? $this->config['default'] : null;
        }

        // 检查短信服务商是否存在
        if (empty($gateway)) {
            return $this->result(SmsConstant::SMS_STATUS_FAIL, sprintf("[%s] 短信服务商不存在", $gateway));
        }

        // 获取短信服务商配置项
        // 如果传入配置参数则优先使用传入的配置参数
        if (!empty($config)) {
            $gateway_config = isset($config) ? $config : null;
        } else {
            $gateway_config = isset($this->config['gateways'][$gateway]) ? $this->config['gateways'][$gateway] : null;
        }
        if (empty($gateway_config)) {
            return $this->result(SmsConstant::SMS_STATUS_FAIL, sprintf("[%s] 短信配置不存在", $gateway));
        }

        // 调用短信服务商驱动class类和send发送短信方法
        $gateway_driver = isset($gateway_config['driver']) ? $gateway_config['driver'] : null;
        if (!class_exists($gateway_driver)) {
            return $this->result(SmsConstant::SMS_STATUS_FAIL, sprintf("[%s] 短信驱动不存在", $gateway));
        }
        if (!method_exists($gateway_driver, 'send')) {
            return $this->result(SmsConstant::SMS_STATUS_FAIL, sprintf("[%s] 短信send方法不存在", $gateway));
        }

        // 调用短信服务商驱动，发送短信
        // 异常捕获，没有异常则返回短信发送信息
        try {
            $result = (new $gateway_driver($gateway_config))->send($mobile_number, $template_code, $template_params);
            if (isset($result['data'])) {
                $data = [
                    'gateway' => $gateway,
                    'mobile_number' => $mobile_number,
                    'template_code' => $template_code,
                    'template_params' => $template_params,
                    'debug' => $result['data'],
                ];
                $result['data'] = $data;
                //$result['data'] = array_replace_recursive($result['data'], $data);
            }
            return $result;
        } catch (\Exception $e) {
            return $this->result(SmsConstant::SMS_STATUS_FAIL, sprintf("短信发送异常：%s", $e->getMessage()));
        }
    }

    /**
     * 短信发送结果返回封装格式
     *
     * @param int $code
     * @param string $message
     * @param null $data
     * @return array
     */
    public static function result($code, $message = null, $data = null)
    {
        $result = [
            'code' => $code,
            'message' => $message ?: SmsConstant::SMS_STATUS[$code],
        ];
        if (!empty($data)) {
            $result['data'] = $data;
        }
        return $result;
    }

    /**
     * 格式化响应结果
     *
     * @param ResponseInterface $response
     * @return mixed|string
     */
    public static function formatResponse(ResponseInterface $response)
    {
        $content_type = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();
        if (false !== stripos($content_type, 'json') || stripos($content_type, 'javascript')) {
            return json_decode($contents, true);
        } elseif (false !== stripos($content_type, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents)), true);
        }
        return $contents;
    }
}
