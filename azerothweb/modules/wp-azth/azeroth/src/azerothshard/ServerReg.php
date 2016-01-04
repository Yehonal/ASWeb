<?php

namespace Azth;

include_once "defines.php";

ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 0);
ini_set('soap.wsdl_cache', 0);

function executeSoapCommand($command) {

    $soap = new \SoapClient(NULL, Array(
        'location' => 'http://' . SOAP_IP . ':' . SOAP_PORT . '/',
        'uri' => 'urn:TC',
        'style' => SOAP_RPC,
        'login' => SOAP_USER,
        'password' => SOAP_PASS,
        'trace' => 1,
        'keep_alive' => false //keep_alive only works in php 5.4, but meh...
    ));

    try {
        $result = $soap->executeCommand(new \SoapParam($command, 'command'));
        return $result;
    } catch (\Exception $e) {
        return $e;
    }
}

/**
 * 
 * @param type $name
 * @param type $password
 * @param type $email
 * @param type $addon
 * @param type $lock
 * @return \Exception|boolean
 */
function createTcAccountFull($name, $password, $email, $addon, $lock) {

    $res = createTcAccount($name, $password);

    if ($res instanceof \Exception)
        return $res;

    $res = setTcAccountRegMail($name, $email);

    if ($res instanceof \Exception)
        return $res;

    $res = setTcAccountEmail($name, $email);

    if ($res instanceof \Exception)
        return $res;

    $res = setTcAccountAddon($name, $addon);

    if ($res instanceof \Exception)
        return $res;

    if ($lock) {
        $res = banTcAccount($name, -1, "New user waiting for approvement");

        if ($res instanceof \Exception)
            return $res;
    }

    return true;
}

function createTcAccount($name, $password) {
    return executeSoapCommand('account create ' . $name . ' ' . $password);
}

/**
 * This is a static mail that won't change 
 * @param type $username
 * @param type $email
 * @return type
 */
function setTcAccountRegMail($username, $email) {
    $email = strtolower($email);
    return executeSoapCommand('account set sec regmail ' . $username . ' ' . $email . ' ' . $email);
}

function banTcAccount($username, $bantime, $reason) {
    return executeSoapCommand('ban account ' . $username . ' ' . $bantime . ' ' . $reason);
}

function unbanTcAccount($username) {
    return executeSoapCommand('unban account ' . $username);
}

function setTcAccountEmail($username, $email) {
    $email = strtolower($email);
    return executeSoapCommand('account set sec email ' . $username . ' ' . $email . ' ' . $email);
}

function setTcAccountPassword($username, $pass) {
    return executeSoapCommand('account set password ' . $username . ' ' . $pass . ' ' . $pass);
}

function setTcAccountAddon($username, $addon) {
    return executeSoapCommand('account set addon ' . $username . ' ' . $addon);
}

function deleteTcAccount($username) {
    return executeSoapCommand('account delete ' . $username);
}
