<?php

namespace yeedomliu\interfaces\fieldstyle;

use yii\helpers\Inflector;

/**
 * Class Camelize
 *
 * 类似把 "send_email" 转换成 "SendEmail"
 *
 */
class Camelize extends Base
{

    public function handle($name) {
        return Inflector::camelize($name);
    }


}