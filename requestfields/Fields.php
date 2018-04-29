<?php

namespace wii\interfaces\requestfields;

trait Fields
{

    /**
     * 请求字段数组
     *
     * @var array
     */
    protected $fields = [];

    /**
     * @return array
     */
    public function getFields(): array {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields) {
        $this->fields = $fields;

        return $this;
    }


}
