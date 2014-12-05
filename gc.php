<?php PHP_SAPI == 'cli' or die('<h1>:P</h1>');
ini_set('memory_limit','1024M');
set_time_limit(0);
error_reporting(E_ALL | E_STRICT);
require_once 'app/Mage.php';
Mage::app()->getCache()->getBackend()->clean('old');
?>
