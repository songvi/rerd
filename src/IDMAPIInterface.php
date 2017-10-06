<?php
namespace IDM;

use AuthStack\Auths\IAuthStorage;
use AuthStack\AuthStack;

interface IDMAPIInterface{

    /**
     * @param StandardClaims $sClaims
     * @param array $extClaims
     * @return
     */
    public function updateIdentity(StandardClaims $sClaims, array $extClaims = []);

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
                                   array $extraClaims = []);

    /**
     *
     */
    public function getIdentity($uuid);

    /**
     * Do login for user with password
     * @param string|string $user string
     * @param string|string $password string
     * @param AuthStack $authStack
     *
     * @return [bool, StandardClaims]
     */
    public function login(string $user, string $password, AuthStack $authStack);

    /**
     * @param $uuid
     * @return
     */
    public function logout($uuid);

    /**
     * @param $oldpwd
     * @param $newpwd
     * @return bool
     */
    public function resetPassword(string $oldpwd, string $newpwd);

    /**
     * @param $uid
     * @return
     */

    public function forgotPwd(string $uid);
}
