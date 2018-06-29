<?php

namespace wii\interfaces\requestfields;

trait CacheKey
{

    /**
     * 缓存key
     *
     * @var string
     */
    protected $cacheKey = '';

    /**
     * @return string
     */
    public function getCacheKey(): string {
        return $this->cacheKey;
    }

    /**
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setCacheKey(string $cacheKey) {
        $this->cacheKey = $cacheKey;

        return $this;
    }


}