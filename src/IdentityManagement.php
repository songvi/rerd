<?php

namespace IDM;

use Psr\SimpleCache\CacheInterface;

class IdentityManagement {
    protected $storage;
    protected $cacheService;

    /**
     * @param IIDMStorage $storage
     */
    public function __construct(IIDMStorage $storage, CacheInterface $cacheService){
        $this->storage = $storage;
        $this->cacheService = $cacheService;
    }

    /**
     *
     */
    public function isExisted($uuid){
        return false;
    }

    public function isExisted2($uid, $authSource){
        return false;
    }
}

