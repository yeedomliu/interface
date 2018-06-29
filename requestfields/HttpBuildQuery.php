<?php

namespace wii\interfaces\requestfields;

trait HttpBuildQuery
{

    /**
     * http_build_query处理
     *
     * @var boolean
     */
    protected $httpBuildQuery = false;

    /**
     * @return bool
     */
    public function isHttpBuildQuery(): bool {
        return $this->httpBuildQuery;
    }

    /**
     * @param bool $httpBuildQuery
     *
     * @return $this
     */
    public function setHttpBuildQuery(bool $httpBuildQuery) {
        $this->httpBuildQuery = $httpBuildQuery;

        return $this;
    }


}
