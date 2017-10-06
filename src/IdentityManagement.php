<?php

namespace IDM;

use AuthStack\Auths\IAuthStorage;
use AuthStack\AuthStack;
use Psr\SimpleCache\CacheInterface;

class IdentityManagement implements IDMInsterface, IDMAPIInterface{
    protected $storage;
    protected $cacheService;

    /**
     * @param IIDMStorage $storage
     * @param CacheInterface $cacheService
     */
    public function __construct(IIDMStorage $storage, CacheInterface $cacheService){
        $this->storage = $storage;
        $this->cacheService = $cacheService;
    }

    /**
     * @param $uuid
     * @return bool
     */
    public function isExisted(string $uuid){
        return false;
    }

    /**
     * @param $uid
     * @param $authSource
     * @return bool
     */
    public function isExisted2(string $uid, string $authSource){
        return false;
    }

    /**
     * @param $uuid
     * @return bool
     */
    public function getStandardClaims(string $uuid)
    {

    }

    /**
     * @param StandardClaims $sClaims
     * @param array $extClaims
     * @return
     */
    public function updateIdentity(StandardClaims $sClaims, array $extClaims = [])
    {

    }

    /**
     * @param string|string $uid
     * @param string|string $password
     * @param StandardClaims $standardClaims
     * @param IAuthStorage $authStorage
     * @param array $extraClaims
     * @return StandardClaims
     */
    public function createIdentity(string $uid,
                                   string $password,
                                   StandardClaims $standardClaims,
                                   IAuthStorage $authStorage,
                                   array $extraClaims = [])
    {

    }

    /**
     *
     */
    public function getIdentity($uuid)
    {

    }

    /**
     * Do login for user with password
     * @param string|string $user string
     * @param string|string $password string
     * @param AuthStack $authStack
     *
     * @return [bool, StandardClaims]
     */
    public function login(string $user, string $password, AuthStack $authStack)
    {

    }

    /**
     * @param $uuid
     * @return
     */
    public function logout($uuid)
    {

    }

    /**
     * @param $oldpwd
     * @param $newpwd
     * @return bool
     */
    public function resetPassword(string $oldpwd, string $newpwd)
    {

    }

    /**
     * @param $uid
     * @return
     */
    public function forgotPwd(string $uid)
    {

    }
}

