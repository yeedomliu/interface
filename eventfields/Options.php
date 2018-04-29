<?php

namespace yeedomliu\interfaces\eventfields;

trait Options
{

    /**
     * 请求选项
     *
     * @var array
     */
    protected $options = [];

    /**
     * @return array
     */
    public function getOptions(): array {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options) {
        $this->options = $options;

        return $this;
    }


}
