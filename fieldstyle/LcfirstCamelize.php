<?php

namespace wii\interfaces\fieldstyle;

use wii\helpers\Inflector;

/**
 * Class LcfirstCamelize
 *
 * 类似把 "send_email" 转换成 "sendEmail"
 *
 * @package wii\interfaces\fieldstyle
 */
class LcfirstCamelize extends Base
{

    public function handle($name) {
        return lcfirst(Inflector::camelize($name));
    }


}