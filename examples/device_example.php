<?php
// 这只是使用样例,不应该直接用于实际生产环境中 !!

require 'config.php';

//
$response = $client->device()->getDevices('a4347a93a2a31da1219755042a5071db');
print_r($response);