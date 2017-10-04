<?php

namespace IDM;

use IDM\Configs\AbstractConfig;

interface IIDMStorage {
    public function init(AbstractConfig $config);
    public function getUser($uuid);
    public function getUser2($uid, $authSource);
    public function saveUser(UserEntity $user);
    public function isExisted($uuid);
    public function isExisted2($uid, $authSource);
    public function getListUUID(array $criterias = []);
    public function getListUID(array $criterias = []);
}
