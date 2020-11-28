<?php

/*
 * This file is part of Schoost.
 *
 * (c) SmartClass, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoost;

use Schoost\LMS\LMS;

class Authentication extends LMS
{
    protected $userId = "";
    protected $userSchoolId = "";
    protected $userPassword = "";
    protected $authDbIdSetting = "off";
    protected $authDbEmailSetting = "off";
    protected $authDbStdSsnSetting = "off";
    protected $authLdapIdSetting = "off";
    protected $authLdapEmailSetting = "off";
    protected $authLoginTypes = array();

    /* function */
    function setUserId($Id)
    {
        //set user Id
        $this->userId = $Id;
    }

    /* function */
    function setSchoolId($Id)
    {
        //set user school Id
        $this->userSchoolId = $Id;
    }

    /* function */
    function setUserPassword($pwd)
    {
        //set user password
        $this->userPassword = $pwd;
    }

    /* function */
    function setAuthDbIdSetting($onoff)
    {
        //set db authentication aid setting
        $this->authDbIdSetting = $onoff;
    }

    /* function */
    function setAuthDbEmailSetting($onoff)
    {
        //set db authentication email setting
        $this->authDbEmailSetting = $onoff;
    }

    /* function */
    function setAuthDbStdSsnSetting($onoff)
    {
        //set db authentication email setting
        $this->authDbStdSsnSetting = $onoff;
    }

    /* function */
    function setAuthLdapIdSetting($onoff)
    {
        //set db authentication aid setting
        $this->authLdapIdSetting = $onoff;
    }

    /* function */
    function setAuthLdapEmailSetting($onoff)
    {
        //set db authentication email setting
        $this->authLdapEmailSetting = $onoff;
    }

    /* function */
    function setAuthLoginType($type)
    {
        //set authentication login types
        $this->authLoginTypes[] = $type;
    }

    /* function */

    function checkLdapUser($username, $userpassword)
    {
        global $dbi;

        //check if it is email
        $isEmail = strpos($username, "@");
        if ($isEmail != false) {
            $usernamex = explode("@", $username);
            $username = $usernamex[0];
        }

        //ldap attributes
        $ldapattr = array("memberof", "sn", "givenname", "samaccountname", "distinguishedname", "mail", "description", "cn");

        //ldap servers
        $ldapServers = $dbi->orderBy("branchID", "ASC")->get(_LDAP_SERVERS_);
        foreach ($ldapServers as $ldapServer) {
            $adServer = $ldapServer["ldap_port"] == "389" ? "ldap://" . $ldapServer["ldap_server"] : ($ldapServer["ldap_port"] == "636" ? "ldaps://" . $ldapServer["ldap_server"] : "error");
            $ldap = ldap_connect($adServer, $ldapServer["ldap_port"]);
            if ($ldap) {
                //$ldaprdn = $ldapServer['ldap_bind_dn'] . "\\" . $username;
                $ldaprdn = $ldapServer['ldap_bind_dn'];
                //ldap connection parameters
                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
                //bind connection
                $bind = @ldap_bind($ldap, $ldaprdn, $userpassword);

                if ($bind) {
                    //$filter = "(sAMAccountName=$username)";
                    $filter = "(uid=$username)";
                    $result = ldap_search($ldap, $ldapServer['ldap_base_dn'], $filter, $ldapattr);
                    //ldap_sort($ldap, $result, "sn");
                    $info = ldap_get_entries($ldap, $result);

                    if ($info['count'] == 1) return $ldapServer["userType"];
                }
            }
        }

        return false;
    }

    /* function */

    function getAuthentication()
    {
        $authInfo = $this->getAuthenticationInfo();

        if ($authInfo["pwd"] == $this->userPassword) return true;
        else return false;
    }

    /* function */

    function getAuthenticationInfo()
    {
        global $dbi, $globalZone;

        $dbi->join(_USER_TYPES_ . " t", "t.typeID=u.userType", "INNER");
        $dbi->join(_SUBELER_ . " s", "s.subeID=u.ySubeKodu", "LEFT");

        if ($this->authDbStdSsnSetting == "on") {
            if ($this->authDbIdSetting == "on" && $this->authDbEmailSetting == "on") $dbi->where("(u.aid=? OR u.email=? OR FIND_IN_SET(?, ogrIDs) > 0)", array($this->userId, $this->userId, $this->userId));
            else if ($this->authDbEmailSetting == "on") $dbi->where("(u.email=? OR FIND_IN_SET(?, ogrIDs) > 0)", array($this->userId, $this->userId));
            else if ($this->authDbIdSetting == "on") $dbi->where("(u.aid=? OR FIND_IN_SET(?, ogrIDs) > 0)", array($this->userId, $this->userId));

            else if ($this->authLdapIdSetting == "on" && $this->authLdapEmailSetting == "on") $dbi->where("(u.ldapAid=? OR u.email=?)", array($this->userId, $this->userId));
            else if ($this->authLdapEmailSetting == "on") $dbi->where("u.email", $this->userId);
            else if ($this->authLdapIdSetting == "on") $dbi->where("u.ldapAid", $this->userId);
        } else {
            if ($this->authDbIdSetting == "on" && $this->authDbEmailSetting == "on") $dbi->where("(u.aid=? OR u.email=?)", array($this->userId, $this->userId));
            else if ($this->authDbEmailSetting == "on") $dbi->where("u.email", $this->userId);
            else if ($this->authDbIdSetting == "on") $dbi->where("u.aid", $this->userId);

            else if ($this->authLdapIdSetting == "on" && $this->authLdapEmailSetting == "on") $dbi->where("(u.ldapAid=? OR u.email=?)", array($this->userId, $this->userId));
            else if ($this->authLdapEmailSetting == "on") $dbi->where("u.email", $this->userId);
            else if ($this->authLdapIdSetting == "on") $dbi->where("u.ldapAid", $this->userId);
        }

        $dbi->where("u.active", "1");
        $dbi->where("t.loginType", $this->authLoginTypes, "IN");
        if ($globalZone != "admin") {
            $authenticationInfo = $dbi->getOne(_USERS_ . " u", "u.id, u.aid, u.pwd, u.email, u.accessToken, u.picture, u.name, u.lastName, u.ySubeKodu, s.subeID, s.stype, u.userType, u.expireDate, u.firebaseId, u.firebaseToken, u.pwdPlain, u.id, u.lmsUserId, t.loginType");
        } else {
            $authenticationInfo = $dbi->getOne(_USERS_ . " u", "u.id, u.aid, u.pwd, u.email, u.accessToken, u.picture, u.name, u.lastName, u.ySubeKodu, s.subeID, s.stype, u.userType, u.expireDate, u.firebaseId, u.firebaseToken, u.pwdPlain, u.id, t.loginType");
        }
        //make school id 0 if it is null for hq
        if (is_null($authenticationInfo["ySubeKodu"])) $authenticationInfo["ySubeKodu"] = "0";

        //check picture
        if (empty($authenticationInfo["picture"])) $authenticationInfo["picture"] = "https://schst.in/nopicture";

        //school id is comint then check if it has a transfer id
        if ($this->userSchoolId != "" && $authenticationInfo["ySubeKodu"] != $this->userSchoolId) {
            //get defautl season db
            $defaultSeasonDB = $dbi->where("ontanimli", "on")->where("aktif", "on")->getValue(_DONEMLER_, "veritabani");

            $dbi->join($defaultSeasonDB . ".personel p", "p.perID=t.perId", "INNER");
            $dbi->where("p.tckimlikno", $authenticationInfo["aid"]);
            $dbi->where("t.mainSchoolId", $authenticationInfo["ySubeKodu"]);
            $getTransferSchoolIds = $dbi->getValue($defaultSeasonDB . ".personel_transfer t", "transferSchoolId", null);

            if (in_array($this->userSchoolId, $getTransferSchoolIds)) $authenticationInfo["ySubeKodu"] = $this->userSchoolId;
        }

        //create user in lms if does not exist
        if ($globalZone != "admin") {
            //set lms info
            //$this->lmsSetSchoolInfo($this->userSchoolId);

            $this->lmsSetSchoolInfo($authenticationInfo["ySubeKodu"]);

            /*
            //crate lms user if exists
            $criteria = array(
                'key'   => 'username',
                'value' => $authenticationInfo["aid"]
            );
            $getLmsUser = $this->lmsGetUser($criteria);
            */

            if (empty($authenticationInfo["lmsUserId"])) {
                if ($authenticationInfo["ySubeKodu"] == "0" || $authenticationInfo["stype"] == "campus" || $authenticationInfo["ySubeKodu"] == "") {
                    $insKeys = array();

                    $moodleInsKey = $dbi->get(_MOODLE_CONFIG_, null, "moodleInsKey");
                    foreach ($moodleInsKey as $moodleKey) {
                        $insKeys[] = array('institutionkey' => $moodleKey["moodleInsKey"]);
                    }

                    $users = array(
                        'username' => $authenticationInfo["aid"],
                        'password' => $authenticationInfo["pwdPlain"],
                        'firstname' => $authenticationInfo["name"],
                        'lastname' => $authenticationInfo["lastName"],
                        'email' => $authenticationInfo["email"],
                        'auth' => 'lti'
                    );

                    $createLMSUser = $this->lmsCreateManagerUsers($insKeys, $users);

                } else {
                    $users = array(
                        'username' => $authenticationInfo["aid"],
                        'password' => $authenticationInfo["pwdPlain"],
                        'firstname' => $authenticationInfo["name"],
                        'lastname' => $authenticationInfo["lastName"],
                        'email' => $authenticationInfo["email"],
                        'auth' => 'lti'
                    );

                    //If User Type is Student
                    if ($authenticationInfo["userType"] == "8") $users["studentno"] = $this->createStudentNo($authenticationInfo["id"]);

                    $createLMSUser = $this->lmsCreateUsers($users);

                }

                if (!empty($createLMSUser)) {
                    $queryData = array('lmsUserId' => $createLMSUser[0]["id"]);

                    $dbi->where("aid", $authenticationInfo["aid"]);
                    $dbi->where("id", $authenticationInfo["id"]);
                    $update = $dbi->update(_USERS_, $queryData);
                }
            }
            /*
            else
            {
                $dbi->where("aid", $authenticationInfo["aid"]);
                $dbi->where("id", $authenticationInfo["id"]);
                $getLmsUserId = $dbi->getValue(_USERS_, "lmsUserId");

                if(empty($getLmsUserId))
                {
                    $queryData = array('lmsUserId' => $getLmsUser["users"][0]["id"]);

                    $dbi->where("aid", $authenticationInfo["aid"]);
                    $dbi->where("id", $authenticationInfo["id"]);
                    $update = $dbi->update(_USERS_, $queryData);
                }
            }
            */

        }

        //return info
        return $authenticationInfo;
    }
}