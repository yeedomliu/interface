<?php

namespace wii\interfaces\eventfields;

trait Method
{

    /**
     * method
     *
     * @var string
     */
    protected $method = '';

    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod(string $method) {
        $this->method = $method;

        return $this;
    }


}
