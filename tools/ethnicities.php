<?php
namespace HistoricalConquest;
include(dirname(__DIR__) . '/types.php');
 
$lines = file(__DIR__ . '/master_list_nationality.csv');
$lines = array_map(function ($l) { return explode("\t",trim($l)); },$lines);
$headers = array_map('strtolower',$lines[0]);
$lines = array_slice($lines,1);
$lines = array_map(function ($l) {
    global $headers;
    $n = [];
    for ( $i = 0; $i < count($headers); $i++) {
        $n[ $headers[$i] ] = $l[$i];
    }
    $races = [
        'ETH_WHITE' => ['European','Celtic','Caucasian','Viking'],
        'ETH_BLACK' => ['Black'],
        'ETH_ARAB' => ['Arab'],
        'ETH_HISPANIC' => ['Hispanic'],
        'ETH_ASIAN' => ['Asian','Polynesian'],
        'ETH_IDIGENOUS' => ['native_american','Native American'],
        'ETH_JEWISH' => [],
    ];
    foreach ( $races as $r=>$m ) {
        foreach ( $m as $origin) {
            if ( empty($origin) )
                continue;
            if ( strstr($n['ethnicity'],$origin) !== FALSE ) {
                if ( defined($r) ) {
                    $n['ethnicity'] = constant($r);
                }
            }
        }
    }
    $religions = array (
        'RELIGION_CATHOLIC' => ['Catholic'],
        'RELIGION_ORTHODOX_CHRISTIAN' => ['Orthodox'],
        'RELIGION_PROTESTANT' => ['Protestant'],
        'RELIGION_CHRISTIAN' => ['Christian'],
        'RELIGION_MUSLIM_SUNNI' => ['Sunni'],
        'RELIGION_MUSLIM_SHIITE' => [],
        'RELIGION_MUSLIM' => ['Muslim','Islam'],
        'RELIGION_BUDDHIST' => ['Buddhist'],
        'RELIGION_HINDU' => ['Hindu'],
        'RELIGION_ATHEIST' => ['Atheist','Marxism'],
        'RELIGION_JEWISH' => ['Jewish'],
        'RELIGION_PAGAN' => ['Neoplatonism','Polytheism','Maat','Egyptian','Monotheist','Monism','Roman','Spiritualism','Pythagorean','paganism','Homeric','Hosmeric','Paganism'],
        'RELIGION_SHINTO' => ['Shinbutsu-shūgō','Shinto'],
        'RELIGION_TAOIST' => ['Confucianism'],
        'RELIGION_AGNOSTIC' => ['Agnostic'],
        'RELIGION_ZOROASTRIAN' => ['Zoroastrianism'],
    );
    foreach ( $religions as $r=>$m ) {
        foreach ( $m as $origin) {
            if ( empty($origin) )
                continue;
            if ( strstr($n['religion'],$origin) !== FALSE ) {
                if ( defined($r) ) {
                    $n['religion'] = constant($r);
                }
            }
        }
    }
    if ( is_numeric($n['ethnicity']) ) {
        echo "UPDATE `hc_card` SET ethnicity = {$n['ethnicity']} WHERE ext_id = '{$n['id']}';".PHP_EOL;
    }
    if ( is_numeric($n['religion']) ) {
        echo "UPDATE `hc_card` SET religion = {$n['religion']} WHERE ext_id = '{$n['id']}';".PHP_EOL;
    }
    return $n;
},$lines);
