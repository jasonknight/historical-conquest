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
$u1 = \get_user_by('ID',1);
$u2 = \get_user_by('ID',2);

//include __DIR__ . '/ajax_get_decks.php';
include __DIR__ . '/ajax_full_game.php';
