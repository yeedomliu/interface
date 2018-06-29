<?php

namespace wii\interfaces\fieldstyle;

use wii\helpers\Inflector;

/**
 * Class Camelize
 *
 * 类似把 "send_email" 转换成 "SendEmail"
 *
 * @package wii\interfaces\fieldstyle
 */
class Camelize extends Base
{

    public function handle($name) {
        return Inflector::camelize($name);
    }


}