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
        print_r($resp);
        exit;
    }
    echo "PASSED: $msg" . PHP_EOL;
}
$u1 = \get_user_by('ID',1);
$u2 = \get_user_by('ID',2);
function http_build_query_for_curl( $arrays, &$new = array(), $prefix = null ) {

    if ( is_object( $arrays ) ) {
        $arrays = get_object_vars( $arrays );
    }

    foreach ( $arrays AS $key => $value ) {
        $k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
        if ( is_array( $value ) OR is_object( $value )  ) {
            http_build_query_for_curl( $value, $new, $k );
        } else {
            $new[$k] = $value;
        }
    }
}
function ajax_post($data) {
    $old_data = $data;
    $h = curl_init();
    $header = [
        'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0',
        'Referer: http://localtest',
    ];
    $url = "http://localhost/wp-admin/admin-ajax.php";
    curl_setopt($h, CURLOPT_HTTPHEADER, $header);
    curl_setopt($h, CURLOPT_HEADER, true);
    curl_setopt($h, CURLOPT_URL, $url);
    curl_setopt($h, CURLOPT_POST, 1);
    curl_setopt($h, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($h, CURLOPT_ENCODING, "");
    curl_setopt($h, CURLOPT_MAXREDIRS, 10);
    curl_setopt($h, CURLOPT_TIMEOUT, 30);
    curl_setopt($h, CURLOPT_VERBOSE,true);
    $verbose = fopen('php://temp','w+');
    curl_setopt($h, CURLOPT_STDERR,$verbose);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($h, CURLOPT_SSL_VERIFYHOST,false);     
    curl_setopt($h, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($h, CURLOPT_RETURNTRANSFER,true);
    http_build_query_for_curl($data,$post_data);
    curl_setopt($h, CURLOPT_POSTFIELDS,$post_data);
    $response = curl_exec($h);
    $struct = new \stdClass; 
    $header_size = curl_getinfo($h, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $content = substr($response, $header_size);
    $struct->data = $data;
    $struct->post_data = $post_data;
    $struct->error = \curl_error($h);
    $struct->errorno = \curl_errno($h);
    $struct->response = $response;
    $struct->head = $header;
    $struct->body = json_decode($content,true);
    rewind($verbose);
    $struct->log = stream_get_contents($verbose);
    fclose($verbose);
    curl_close($h);
    return $struct;
}
include __DIR__ . '/ajax_get_decks.php';

