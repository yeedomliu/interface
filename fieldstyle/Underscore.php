<?php

namespace yeedomliu\interfaces\fieldstyle;

use yii\helpers\Inflector;

/**
 * Class Underscore
 *
 * 把 "CamelCased" 转换成 "underscored_word"
 */
class Underscore extends Base
{

    public function handle($name) {
        return Inflector::underscore($name);
    }


}