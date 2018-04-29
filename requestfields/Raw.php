<?php

namespace yeedomliu\interfaces\requestfields;

trait Raw
{

    /**
     * 是否返回原始内容
     *
     * @var bool
     */
    protected $raw = false;

    /**
     * @return bool
     */
    public function isRaw(): bool {
        return $this->raw;
    }

    /**
     * @param bool $raw
     *
     * @return Raw
     */
    public function setRaw(bool $raw) {
        $this->raw = $raw;

        return $this;
    }


}
