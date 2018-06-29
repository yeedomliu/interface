<?php

namespace wii\interfaces\proxy;

use wii\base\Component;
use wii\interfaces\requestfields\ExcludeFields;
use wii\interfaces\requestfields\Fields;
use wii\interfaces\requestfields\Headers;
use wii\interfaces\requestfields\HttpBuildQuery;
use wii\interfaces\requestfields\JsonEncodeFields;
use wii\interfaces\requestfields\Method;
use wii\interfaces\requestfields\Options;
use wii\interfaces\requestfields\Prefix;
use wii\interfaces\requestfields\Raw;
use wii\interfaces\requestfields\Url;

abstract class Base extends Component
{

    use Prefix, Url, Fields, Raw, Method, JsonEncodeFields, Headers, ExcludeFields, Options, HttpBuildQuery;

    abstract public function getContent();

}