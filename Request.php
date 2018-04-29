<?php

namespace yeedomliu\interfaces;

use yeedomliu\interfaces\requestfields\ExcludeFields;
use yeedomliu\interfaces\requestfields\Fields;
use yeedomliu\interfaces\requestfields\Headers;
use yeedomliu\interfaces\requestfields\JsonEncodeFields;
use yeedomliu\interfaces\requestfields\Method;
use yeedomliu\interfaces\requestfields\Options;
use yeedomliu\interfaces\requestfields\Prefix;
use yeedomliu\interfaces\requestfields\Raw;
use yeedomliu\interfaces\requestfields\Url;
use yii\base\Component;

/**
 * 接口请求
 *
 * 接口保持最简洁的功能，其它功能通过事件注入进来
 *
 */
class Request extends Component
{

    use Prefix, Url, Fields, Raw, Method, JsonEncodeFields, Headers, ExcludeFields, Options;

    const METHOD_GET = 'get';

    const METHOD_POST = 'post';

    const EVENT_REQUEST_BEFORE = 'request_before';

    const EVENT_REQUEST_AFTER = 'request_after';

    const EVENT_REQUEST_EXCEPTION = 'request_exception';

    /**
     * 获取完整请求路径
     *
     * @return string
     */
    public function getFullUrl() {
        return $this->prefix . $this->getUrl();
    }

    /**
     * 执行请求
     *
     * @return mixed
     */
    public function request() {

        try {
            // 请求字段处理
            {
                $url = $this->getFullUrl();
                $ch = curl_init();
                $fields = $this->getFields();
                if ($this->isPostMethod()) {
                    if ($this->isJsonEncodeFields()) {
                        $fields = json_encode($fields);
                    }
                    $this->addOption(CURLOPT_POST, true);
                    $this->addOption(CURLOPT_POSTFIELDS, $fields);
                } else {
                    $fieldString = is_array($fields) ? http_build_query($fields) : $fields;
                    $url .= preg_match('/\?/is', $url) ? "&" : "?";
                    $url .= $fieldString;
                }
            }

            // 请求处理
            {
                $this->addOptions([
                                      CURLOPT_HTTPHEADER     => $this->getHeaders(),
                                      CURLOPT_URL            => $url,
                                      CURLOPT_RETURNTRANSFER => 1,
                                  ]);
                $event = (new Event())->setUrl($url)->setFields($fields)->setMethod($this->getMethod())->setOptions($this->getOptions())->setRequestObj($this);
                $this->trigger(self::EVENT_REQUEST_BEFORE, $event);

                curl_setopt_array($ch, $this->getOptions());
                $result = curl_exec($ch);
                $status = curl_getinfo($ch);
                $this->trigger(self::EVENT_REQUEST_AFTER, $event->setResult($result)->setStatus($status));
            }

            // 异常处理
            {
                if ($result === false) {
                    throw new \Exception("网络错误");
                }

                if (intval($status["http_code"]) != 200) {
                    throw new \Exception("unexpected http code " . intval($status["http_code"]));
                }

                $return = ! $this->isRaw() ? json_decode($result, true) : $result;
                if (is_array($return)) {
                    if (0 != $return['errcode']) {
                        throw new \Exception("调用接口出现错误[{$return['errmsg']}]");
                    }
                }
            }

            return $return;
        } catch (\Exception $e) {
            $this->trigger(self::EVENT_REQUEST_EXCEPTION, $event);
            throw $e;
        }
    }

}