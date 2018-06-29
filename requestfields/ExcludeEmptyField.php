<?php

namespace wii\interfaces\requestfields;

trait ExcludeEmptyField
{

    /**
     * 排除空字段
     *
     * @var boolean
     */
    protected $excludeEmptyField = false;

    /**
     * @return bool
     */
    public function isExcludeEmptyField(): bool {
        return $this->excludeEmptyField;
    }

    /**
     * @param bool $excludeEmptyField
     *
     * @return $this
     */
    public function setExcludeEmptyField(bool $excludeEmptyField) {
        $this->excludeEmptyField = $excludeEmptyField;

        return $this;
    }

    public function getExcludeEmptyField() {
        return $this->excludeEmptyField;
    }


}
