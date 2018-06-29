<?php

namespace yeedomliu\interfaces;

use wii\helpers\Inflector;
use wii\interfaces\fieldstyle\LcfirstCamelize;
use wii\interfaces\requestfields\CacheKey;
use wii\interfaces\requestfields\CacheTime;
use wii\interfaces\requestfields\ExcludeEmptyField;
use wii\interfaces\requestfields\FullUrl;
use wii\interfaces\requestfields\GetFields;
use wii\interfaces\requestfields\PostFields;
use wii\interfaces\requestfields\PostRequest;
use wii\interfaces\requestfields\Url;

class Base
{

    use CacheTime, CacheKey, FullUrl, ExcludeEmptyField, PostRequest, Url, PostFields, GetFields;

    /**
     * 初始化操作
     */
    public function init() {
    }

    /**
     * 请求url地址
     *
     * @return string
     */
    public function url() {
        return $this->getUrl() ? $this->getUrl() : '';
    }

    /**
     * 获取带参数路径
     *
     * @return string
     */
    public function fullUrl() {
        $url = $this->url();
        if ($this->defaultGetFields()) {
            $getFields = [];
            foreach ($this->defaultGetFields() as $name => $value) {
                $getFields[] = "{$name}={$value}";
            }
            $url .= preg_match('/\?/is', $url) ? "&" : "?";
            $url .= join('&', $getFields);
        }

        return $url;
    }

    /**
     * 请求前缀
     *
     * @return string
     */
    public function requestPrefix() {
        return '';
    }

    /**
     * 自定义处理request对象
     *
     * @param \wii\interfaces\Request $requestObj
     *
     * @return \wii\interfaces\Request
     */
    public function requestHandle(Request $requestObj) {
        return $requestObj;
    }

    /**
     * 获取request对象
     *
     * @return \wii\interfaces\Request
     */
    public function getRequestObj() {
        $requestObj = (new Request());
        $this->isPostRequest() ? $requestObj->setPostMethod() : $requestObj->setGetMethod();

        //        $requestObj = $this->isPostRequest() ? $this->getPostRequest() : $this->getGetRequest();

        return $this->requestHandle($requestObj->setPrefix($this->requestPrefix()));
    }

    /**
     * 对字段名字进行处理（可以进行不同风格的转换）
     *
     * @return \wii\interfaces\fieldstyle\LcfirstCamelize
     */
    public function getFieldNameHandleObj() {
        return new LcfirstCamelize();
    }

    /**
     * 请求开始
     *
     * @return $this
     */
    public function eventStart() {
        \yii\base\Event::off(Request::className(), Request::EVENT_REQUEST_START);

        return $this;
    }

    /**
     * 请求事件before处理
     * 1.https请求处理
     *
     * @return $this
     */
    public function eventBefore() {
        \yii\base\Event::off(Request::className(), Request::EVENT_REQUEST_BEFORE);
        \yii\base\Event::on(Request::className(), Request::EVENT_REQUEST_BEFORE, function (Event $event) {
            if (stripos($event->getUrl(), "https://") !== false) {
                $event->getRequestObj()->addOption(CURLOPT_SSL_VERIFYPEER, false)->addOption(CURLOPT_SSL_VERIFYHOST, false)->addOption(CURLOPT_SSLVERSION, 1);
            }
        });

        return $this;
    }

    /**
     * 请求事件after处理
     * 1.记录日志
     *
     * @return $this
     */
    public function eventAfter() {
        \yii\base\Event::off(Request::className(), Request::EVENT_REQUEST_AFTER);
        \yii\base\Event::on(Request::className(), Request::EVENT_REQUEST_AFTER, function (Event $event) {
            \Yii::info([
                           'url'     => $event->getUrl(),
                           'fields'  => $event->getFields(),
                           'method'  => $event->getMethod(),
                           'result'  => $event->getResult(),
                           'header'  => $event->getHeaders(),
                           'options' => $event->getOptions(),
                           'status'  => $event->getStatus(),
                       ], 'request.result');
        });

        return $this;
    }

    /**
     * 请求事件异常处理
     *
     * @return $this
     */
    public function eventException() {
        \yii\base\Event::off(Request::className(), Request::EVENT_REQUEST_EXCEPTION);
        \yii\base\Event::on(Request::className(), Request::EVENT_REQUEST_EXCEPTION, function (Event $event) {
            \Yii::info([
                           'url'            => $event->getUrl(),
                           'fields'         => $event->getFields(),
                           'method'         => $event->getMethod(),
                           'result'         => $event->getResult(),
                           'options'        => $event->getOptions(),
                           'status'         => $event->getStatus(),
                           'exception_msg'  => $event->getException()->getMessage(),
                           'exception_code' => $event->getException()->getCode(),
                       ], 'request.exception');
        });

        return $this;
    }

    /**
     * 获取处理完字段数组
     * 1.获取trait字段数组
     * 2.加入默认字段、自定义字段、去除排除字段
     *
     * @return array
     */
    public function getHandledFields() {
        $obj = new \ReflectionClass(get_called_class());
        $traits = $obj->getTraits();
        if ($obj->getParentClass()) {
            $parentTraits = $obj->getParentClass()->getTraits();
            if ($parentTraits) {
                $traits = array_merge($traits, $parentTraits);
            }
        }
        $fields = [];
        if ($traits) {
            foreach ($traits as $trait) {
                $props = $trait->getProperties();
                if (empty($props)) {
                    continue;
                }
                foreach ($props as $prop) {
                    $name = $this->getFieldNameHandleObj()->handle($prop->name);
                    $method = 'get' . Inflector::camelize($prop->name);
                    $fields[ $name ] = call_user_func_array([$this, $method], []);
                }
            }
        }
        $fields = array_merge($fields, $this->defaultFields(), $this->customFields());
        if ($this->excludeFields()) {
            foreach ($this->excludeFields() as $excludeField) {
                unset($fields[ $excludeField ]);
            }
        }

        // 排除空字段值
        if ($this->isExcludeEmptyField()) {
            foreach ($fields as $key => $value) {
                if (is_object($value)) {
                    continue;
                }
                if (is_array($value)) {
                    if (empty($value)) {
                        unset($fields[ $key ]);
                    }
                } elseif (0 == strlen($value)) {
                    unset($fields[ $key ]);
                }
            }
        }

        return $fields;
    }

    /**
     * 开始执行请求
     *
     * @return mixed
     */
    public function start() {
        $this->init();

        $requestObj = $this->getRequestObj();
        // 请求头部处理
        if ($this->requestHeaders()) {
            foreach ($this->requestHeaders() as $key => $value) {
                $requestObj->addHeader($key, $value);
            }
        }

        // 把trait的属性都转换为字段名
        $fields = $this->getHandledFields();

        // 事件处理
        $this->eventStart()->eventBefore()->eventAfter()->eventException();

        $result = $requestObj->setHttpBuildQuery($this->httpBuildQuery())
                             ->setJsonEncodeFields($this->jsonEncodeFields())
                             ->setFields($fields)
                             ->setExcludeFields($this->excludeFields())
                             ->setUrl($this->fullUrl())
                             ->setFullUrl($this->getFullUrl())
                             ->setRaw($this->returnRaw() ? true : false)
                             ->setCacheTime($this->getCacheTime())
                             ->setCacheKey($this->getCacheKey())
                             ->request();

        $this->checkResult($result);

        return $this->customOutput($result);
    }

    /**
     * 自定义输出
     *
     * @param $result
     *
     * @return mixed
     */
    public function customOutput($result) {
        return $result;
    }

    /**
     * 检查结果是否正确
     *
     * @param $result
     */
    public function checkResult($result) {
    }

    /**
     * 自定义字段
     *
     * @return array
     */
    public function customFields() {
        return [];
    }

    /**
     * 排除字段
     *
     * @return array
     */
    public function excludeFields() {
        return [];
    }

    /**
     * 默认get参数
     *
     * @return array
     */
    public function defaultGetFields() {
        return $this->getGetFields() ? $this->getGetFields() : [];
    }

    /**
     * 默认字段
     *
     * @return array
     */
    public function defaultFields() {
        return $this->getPostFields() ? $this->getPostFields() : [];
    }

    /**
     * 返回原始数据
     *
     * @return bool
     */
    public function returnRaw() {
        return false;
    }

    /**
     * json_encode字段值
     *
     * @return bool
     */
    public function jsonEncodeFields() {
        return false;
    }

    /**
     * http_build_query处理
     *
     * 如果提交多维数组需要设置为true对请求的字段进行处理
     *
     * @return bool
     */
    public function httpBuildQuery() {
        return false;
    }

    /**
     * 请求头部信息数组，以key/value形式
     *
     * @return array
     */
    public function requestHeaders() {
        return [];
    }

}