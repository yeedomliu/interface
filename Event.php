<?php

namespace yeedomliu\interfaces;

use wii\interfaces\eventfields\Exception;
use wii\interfaces\eventfields\Fields;
use wii\interfaces\eventfields\Method;
use wii\interfaces\eventfields\Options;
use wii\interfaces\eventfields\Result;
use wii\interfaces\eventfields\Status;
use wii\interfaces\eventfields\Url;
use wii\interfaces\requestfields\Headers;

/**
 * 接口请求
 *
 *
 */
class Event extends \yii\base\Event
{

    use Fields, Method, Options, Result, Url, Status, Exception, Headers;

    /**
     * @var \wii\interfaces\Request
     */
    protected $requestObj;

    /**
     * @return \wii\interfaces\Request
     */
    public function getRequestObj(): \wii\interfaces\Request {
        return $this->requestObj;
    }

    /**
     * @param \wii\interfaces\Request $requestObj
     *
     * @return $this
     */
    public function setRequestObj(\wii\interfaces\Request $requestObj) {
        $this->requestObj = $requestObj;

        return $this;
    }


}