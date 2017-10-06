<?php

namespace IDM\Services;

use AuthStack\Auths\AuthLdap;
use AuthStack\Auths\AuthSql;
use AuthStack\Configs\AuthType;
use AuthStack\Configs\LdapConfig;
use AuthStack\Configs\MySQLConfig;
use AuthStack\Exceptions\ConfigNotFoundException;
use AuthStack\Exceptions\ConfigSyntaxException;
use AuthStack\Logs\LogFile;
use AuthStack\Logs\LogType;
use Dibi\Drivers\SqlsrvDriver;
use IDM\SqlStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigService
{
    protected $config;

    /**
     * init get the path to file config and return a list of auth config
     * @param $yamlFilePath
     * @return array() of auth
     * @throws ConfigNotFoundException
     * @throws ConfigSyntaxException
     */
    public function init($yamlFilePath)
    {
        if (!is_file($yamlFilePath)) {
            throw new ConfigNotFoundException();
        };

        try {
            $this->config = Yaml::parse(file_get_contents($yamlFilePath));
        } catch (\Exception $e) {
            throw new ConfigSyntaxException($yamlFilePath);
        }
    }

    public function getIDMStorage(){
        $config = MySQLConfig($this->config['authentication']['userstorage']['sqlconnection']);
        return new SqlStorage($config);
    }
}
