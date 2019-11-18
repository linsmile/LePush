<?php
require __DIR__ . '/../autoload.php';

use LePush\LePush as LePush;

$app_key = getenv('app_key');
$master_secret = getenv('master_secret');
$registration_id = getenv('registration_id');
$options  =   [
    'logFile'  => '', //错误日志文件路径
];
$client = new LePush($app_key, $master_secret, $options);
