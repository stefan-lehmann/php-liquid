<?php

define('LIQUID_INCLUDE_PREFIX', '');

$payload = json_decode(file_get_contents("php://input"), true);
$json = $payload.json;
$rootPath = (isset($payload['root'])) ? $payload['root'] : dirname(__DIR__).'/source/styleguide/components/';

if (substr($rootPath, -1) !== '/') {
  $rootPath .= '/';
}
$template = (isset($payload['template'])) ? $payload['template'] : file_get_contents($rootPath.$payload['filename']);
$data = (isset($payload['data'])) ? $payload['data'] : array();

require_once('../Liquid.class.php');
$liquid = new LiquidTemplate($rootPath);

$liquid->parse($template);
print $liquid->render($data);