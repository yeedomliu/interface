<?php

namespace wii\interfaces\requestfields;

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
     * @return bool
     */
    public function getRaw() {
        return $this->isRaw();
    }

    /**
     * @param bool $raw
     *
     * @return $this
     */
    public function setRaw(bool $raw) {
        $this->raw = $raw;

        return $this;
    }


}
