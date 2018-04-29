<?php

namespace yeedomliu\interfaces;

use wii\helpers\Inflector;
use wii\interfaces\fieldstyle\LcfirstCamelize;

class Base
{

    /**
     * @return $this
     */
    public function getPostRequest() {
        return (new Request())->setPostMethod();
    }

    /**
     * @return $this
     */
    public function getGetRequest() {
        return (new Request())->setGetMethod();
    }

    /**
     * 是否是post请求
     *
     * @return bool
     */
    public function isPostRequest() {
        return false;
    }

    /**
     * 请求url地址
     *
     * @return string
     */
    public function url() {
        return '';
    }

    /**
     * 请求前缀
     *
     * @return string
     */
    public function requestPrefix() {
        return '';
        //        return 'https://qyapi.weixin.qq.com/cgi-bin/';
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
        $requestObj = $this->isPostRequest() ? $this->getPostRequest() : $this->getGetRequest();

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
        });

        return $this;
    }

    /**
     * 开始执行请求
     *
     * @return mixed
     */
    public function start() {
        $requestObj = $this->getRequestObj();

        // 把trait的属性都转换为字段名
        $obj = new \ReflectionClass(get_called_class());
        $traits = $obj->getTraits();
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

        $this->eventBefore()->eventAfter()->eventException();

        return $requestObj->setFields($fields)->setExcludeFields($this->excludeFields())->setUrl($this->url())->request();
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
     * 默认字段
     *
     * @return array
     */
    public function defaultFields() {
        return [];
    }

}