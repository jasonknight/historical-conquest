<?php
namespace HistoricalConquest;
$resp = null;
$data = null;
require_once(dirname(__DIR__) . '/core.php');
require_once(dirname(__DIR__) . '/types.php');
require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php');
function test($val,$msg) {
    global $resp;
    if ( ! $val ) {
        echo "FAILED: $msg" . PHP_EOL;
        file_put_contents(__DIR__ . '/test.log',print_r($resp,true) . PHP_EOL);
        //print_r($resp);
        exit;
    }
    echo "PASSED: $msg" . PHP_EOL;
}
$data = [
    'action' => 'hc-user-registered',
    'hc-return-url' => 'http://localhost',
];
$r = ajax_post($data,'http://localhost');
var_dump($r);
