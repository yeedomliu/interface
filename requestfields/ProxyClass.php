<?php

namespace wii\interfaces\requestfields;

/**
 * 代理类trait
 *
 * @package wii\interfaces\requestfields
 */
trait ProxyClass
{

    /**
     * 请求前缀
     *
     * @var \wii\interfaces\proxy\Base
     */
    protected $proxyClass;

    /**
     * @return \wii\interfaces\proxy\Base
     */
    public function getProxyClass() {
        return $this->proxyClass;
    }

    /**
     * @param \wii\interfaces\proxy\Base $proxyClass
     *
     * @return $this
     */
    public function setProxyClass($proxyClass) {
        $this->proxyClass = $proxyClass;

        return $this;
    }

}