<?php

namespace IDM;

class StandardClaims implements \JsonSerializable
{
    public $sub;
    public $name;
    public $given_name;
    public $family_name;
    public $middle_name;
    public $nickname;
    public $preferred_username;
    public $profile;
    public $email;
    public $email_verified;
    public $gender;
    public $birthdate;
    public $zoneinfo;
    public $locale;
    public $phone_number;
    public $address;
    public $preferred_lang;
    public $preferred_theme;

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {

    }
}
