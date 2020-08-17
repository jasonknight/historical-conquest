<?php
namespace HistoricalConquest;
include(dirname(__DIR__) . '/types.php');
function get_states() {
    $states = [];
    $lines = array_map(function ($l) { return explode(',',trim($l)); }, file(__DIR__ . '/continents.txt'));
    foreach ( $lines as $line ) {
        $st = strtolower($line[1]);
        $ct = 'CONTINENT_' . str_replace(' ', '_', strtoupper($line[0]));
        $states[$st] = $ct;
    }
    $lines = array_map(function ($l) { return trim($l); }, file(__DIR__ . '/states.txt'));
    foreach ( $lines as $line ) {
        $st = strtolower($line);
        $ct = 'CONTINENT_NORTH_AMERICA';
        $states[$st] = $ct;
    }
    return $states;
} 
$lines = file(__DIR__ . '/master_list_lands.csv');
$lines = array_map(function ($l) { return explode("\t",trim($l)); },$lines);
$headers = array_map('strtolower',$lines[0]);
$lines = array_slice($lines,1);
$lines = array_map(function ($l) {
    global $headers;
    $n = [];
    for ( $i = 0; $i < count($headers); $i++) {
        $n[ $headers[$i] ] = $l[$i];
    }
    $states = get_states();
    $st = trim(strtolower($n['name']));
    if ( $st == 'russia' ) {
        $n['continent'] = 'CONTINENT_EUROPE';
    } else {
        foreach ($states as $key => $val ) {
            if ( $st == $key ) {
                $n['continent'] = $states[strtolower($n['name'])];
            }
        } 
    }
    if ( !isset($n['continent']) ) {
        die("Could not find $st\n");
    } else {
        if ( defined($n['continent']) ) {
            $n['continent'] = constant($n['continent']);
        }
    }
    if ( $n['coastal'] == 'coastal' ) {
        $n['coastal'] = 1;
    } else {
        $n['coastal'] = 0;
    }
    echo "UPDATE `hc_cards` SET continent = {$n['continent']}, is_coastal = {$n['coastal']} WHERE ext_id = '{$n['id']}';" . PHP_EOL;
    return $n;
},$lines);
