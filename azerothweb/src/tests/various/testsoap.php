<?php

ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 0);
ini_set('soap.wsdl_cache', 0);

$username = 'yehonal-gm';
$password = 'admin.2';
$host = "azerothshard.servegame.com";
$soapport = 80;
$command = "server info";

echo "trying on host {$host}:{$soapport}<br>";

$client = new SoapClient(NULL, array(
    "location" => "http://$host:$soapport/",
    "uri" => "urn:TC",
    "style" => SOAP_RPC,
    'login' => $username,
    'password' => $password
        ));

$result = $client->executeCommand(new SoapParam($command, "command"));
echo "Command succeeded! Output:<br />\n";
echo $result;

echo "\n";
