<?php

namespace wii\interfaces\fieldstyle;

use wii\helpers\Inflector;

/**
 * Class Underscore
 *
 * 把 "CamelCased" 转换成 "underscored_word"
 *
 * @package wii\interfaces\fieldstyle
 */
class Underscore extends Base
{

    public function handle($name) {
        return Inflector::underscore($name);
    }


}