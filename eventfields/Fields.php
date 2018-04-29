<?php

namespace wii\interfaces\eventfields;

trait Fields
{

    /**
     * 字段
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
     *
     * @return $this
     */
    public function setFields(array $fields) {
        $this->fields = $fields;

        return $this;
    }


}
