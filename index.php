<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
function __autoload($className) { require_once ('class/'.$className.'.php'); }
$app = new Application();
die($app->run());
