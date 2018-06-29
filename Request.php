<?php

namespace yeedomliu\interfaces;

use linslin\yii2\curl\Curl;
use wii\base\Component;
use wii\helpers\Json;
use wii\interfaces\requestfields\CacheKey;
use wii\interfaces\requestfields\CacheTime;
use wii\interfaces\requestfields\ConnectTimeout;
use wii\interfaces\requestfields\ExcludeEmptyField;
use wii\interfaces\requestfields\ExcludeFields;
use wii\interfaces\requestfields\Fields;
use wii\interfaces\requestfields\FullUrl;
use wii\interfaces\requestfields\Headers;
use wii\interfaces\requestfields\HttpBuildQuery;
use wii\interfaces\requestfields\JsonEncodeFields;
use wii\interfaces\requestfields\Method;
use wii\interfaces\requestfields\Options;
use wii\interfaces\requestfields\Prefix;
use wii\interfaces\requestfields\ProxyClass;
use wii\interfaces\requestfields\Raw;
use wii\interfaces\requestfields\Timeout;
use wii\interfaces\requestfields\Url;
use yii\helpers\VarDumper;

/**
 * 接口请求
 *
 * 接口保持最简洁的功能，其它功能通过事件注入进来
 *
 */
class Request extends Component
{

    use Prefix, Url, FullUrl, Fields, Raw, Method, JsonEncodeFields, Headers, ExcludeFields, Options, HttpBuildQuery, CacheTime, CacheKey, ProxyClass, Timeout, ConnectTimeout, ExcludeEmptyField;

    const METHOD_GET = 'get';

    const METHOD_POST = 'post';

    const EVENT_REQUEST_START = 'request_start';

    const EVENT_REQUEST_BEFORE = 'request_before';

    const EVENT_REQUEST_AFTER = 'request_after';

    const EVENT_REQUEST_EXCEPTION = 'request_exception';

    /**
     * 获取完整请求路径
     *
     * @return string
     */
    public function getFullUrl() {
        if ($this->fullUrl) {
            $url = $this->fullUrl;
        } else {
            $url = $this->getPrefix() . $this->getUrl();
            if ( ! $this->isPostMethod()) {
                $fields = $this->getFields();
                $fieldString = is_array($fields) ? http_build_query($fields) : $fields;
                $url .= preg_match('/\?/is', $url) ? "&" : "?";
                $url .= $fieldString;
            }
        }

        return $url;
    }

    public function getFullFields() {
        $fields = $this->getFields();
        if ($this->isPostMethod()) {
            if ($this->isJsonEncodeFields()) {
                $fields = json_encode($fields);
            }
            if (is_array($fields) && $this->isHttpBuildQuery()) {
                $fields = http_build_query($fields);
            }
        }

        return $fields;
    }

    public function getCacheKey(): string {
        return $this->cacheKey ? $this->cacheKey : "{$this->getFullUrl()}{$this->isPostMethod()}{$this->isRaw()}";
    }

    /**
     * 执行请求
     *
     * @return mixed
     */
    public function request() {
        try {
            $this->trigger(self::EVENT_REQUEST_START);

            // 请求字段处理
            {
                //                $curl = new Curl();

                $url = $this->getFullUrl();
                $ch = curl_init();
                $fields = $this->getFullFields();
                if ($this->isPostMethod()) {
                    $this->addOption(CURLOPT_POST, true);
                    $this->addOption(CURLOPT_POSTFIELDS, $fields);
                }
            }

            // 请求处理
            {
                $event = (new Event())->setUrl($url)->setFields($fields)->setMethod($this->getMethod())->setOptions($this->getOptions())->setHeaders($this->getHeaders())->setRequestObj($this);
                $cacheKey = $this->getCacheKey();
                if ($this->getCacheTime()) {
                    $return = \Wii::app()->cache->get($cacheKey);
                }
                if ( ! empty($return)) {
                    \Wii::info("request[{$url}] from cache");

                    return $return;
                } else {
                    $this->trigger(self::EVENT_REQUEST_BEFORE, $event);
                    if ($this->getProxyClass()) {
                        $return = $this->getProxyClass()
                                       ->setPrefix($this->getPrefix())
                                       ->setUrl($url)
                                       ->setFields($fields)
                                       ->setRaw($this->isRaw())
                                       ->setJsonEncodeFields($this->isJsonEncodeFields())
                                       ->setHeaders($this->getHeaders())
                                       ->setExcludeFields($this->getExcludeFields())
                                       ->setOptions($this->getOptions())
                                       ->setHttpBuildQuery($this->isHttpBuildQuery())
                                       ->setMethod($this->getMethod())
                                       ->getContent();
                    } else {
                        $this->addOptions([
                                              CURLOPT_HTTPHEADER     => $this->getHeaders(),
                                              CURLOPT_URL            => $url,
                                              CURLOPT_RETURNTRANSFER => 1,
                                              CURLOPT_TIMEOUT        => $this->getTimeout(),
                                              CURLOPT_CONNECTTIMEOUT => $this->getConnectTimeout(),
                                          ]);

                        curl_setopt_array($ch, $this->getOptions());
                        $return = curl_exec($ch);
                    }
                    $status = curl_getinfo($ch);
                    $event->setStatus($status);

                    $this->trigger(self::EVENT_REQUEST_AFTER, $event->setResult($return));
                }
            }

            // 异常处理
            {
                //                if ($return === false) {
                //                    throw new \Exception("网络错误");
                //                }

                //                if ($status && intval($status["http_code"]) != 200) {
                //                    throw new \Exception("unexpected http code " . intval($status["http_code"]));
                //                }


                if ( ! $this->isRaw()) {
                    try {
                        $return = Json::decode($return);
                    } catch (\Exception $e) {
                        \Wii::error("解析json异常[{$e->getMessage()}],内容[{$return}]");
                    }
                }
                if (is_array($return)) {
                    if (0 != $return['errcode']) {
                        throw new \Exception("调用接口出现错误[{$return['errmsg']}]");
                    }
                }
            }
            if ($this->getCacheTime()) {
                \Wii::app()->cache->set($cacheKey, $return);
            }

            return $return;
        } catch (\Exception $e) {
            $this->trigger(self::EVENT_REQUEST_EXCEPTION, $event->setException($e));
            throw $e;
        }
    }

}