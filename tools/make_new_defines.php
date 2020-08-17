<?php
namespace HistoricalConquest;
include(dirname(__DIR__) . '/types.php');
include(dirname(__DIR__) . '/core.php');
exit;
$defs = [];
function array_to_defines($a,$iota = 0,$prefix='') {
    global $defs;
    foreach ( $a as $k=>$v) {
        if ( is_array($v) ) {
            $name = strtoupper($prefix . $k);
            echo "define('$name',$iota);\n";
            $iota++;
            $iota = array_to_defines($v,$iota,$prefix . $k . '_'); 
            continue;
        }
        $name = strtoupper($prefix . $v);
        $defs[] = $name;
        echo "define('$name',$iota);\n";
        $iota++;
    }
    return $iota;
}
$nationalities = [];
$lines = file(__DIR__ . '/master_list_nationality.csv');
$lines = array_map(function ($l) { return explode("\t",$l); }, $lines);
$headers = array_map('strtolower',$lines[0]);
$lines = array_slice($lines,1);
$lines = array_map(function ($l) { 
    global $headers;
    global $nationalities;
    $n = [];
    for ( $i = 0; $i < count($headers); $i++ ) {
        $n[ $headers[$i] ] = $l[$i];
    }
    $nat = clean_whitespace($n['nationality']);
    if ( strpos($nat,',') !== FALSE) {
        $nat = explode(',',$nat);
        $nat = $nat[0];
    }
    $nat = strtoupper(str_replace('/','_',$nat));
    if ( $nat == 'SPAIN' ) {
        $nat = "SPANISH";
    }
    if ( $nat == 'GEORGIAN' ) {
        $nat = "RUSSIAN_GEORGIAN";
    }
    if ( $nat == 'ARGENTINE' ) {
        $nat = 'ARGENTINIAN';
    }
    if ( $nat == 'NORWEIGN' ) {
        $nat = "NORWEGIAN";
    }
    if ( $nat == 'HINDU' ) {
        $nat = 'INDIAN';
    }
    if ( $nat == 'MALI' ) {
        $nat = 'MALIAN';
    }
    if ( $nat == 'CHINA' ) {
        $nat = "CHINESE";
    }
    if ( $nat == 'FRANCE' ) {
        $nat = "FRENCH";
    }
    if ( $nat == 'NEW ZEALAND' ) {
        $nat = "KIWI";
    }
    if ( ! in_array($nat,$nationalities) ) {
        $nationalities[] = $nat;
    }
    return $n;
}, $lines);
print_r($nationalities);
exit;
$defs_by_prefix = [];
ob_start();
echo "<?php\nnamespace HistoricalConquest;\n";
foreach ( $defs as $def ) {
    preg_match("/^([A-Z]+)_/",$def,$m);
    $pref = $m[1];
    if ( !isset($defs_by_prefix[$pref]) )
        $defs_by_prefix[$pref] = [];
    $defs_by_prefix[$pref][] = $def;
}
echo "function type_to_name(\$val) { " . "\n";
foreach ( $defs as $def ) {
    echo "\t if ( \$val == $def ) { return '$def'; }" . "\n";
}
echo "}" . "\n";
echo "function name_to_type(\$val) { " . "\n";
foreach ( $defs as $def ) {
    echo "\t if ( \$val == '$def' ) { return $def; }" . "\n";
}
echo "}" . "\n";
echo "function options_by_prefix(\$pref) { " . "\n";
ob_start();
var_export($defs_by_prefix);
$contents = ob_get_contents();
ob_end_clean();
echo "\t \$defs = " . $contents . ';' . "\n";
echo "\n";
echo " return !empty(\$pref) ? \$defs[\$pref] : \$defs;" . "\n";
echo "}" . "\n";

echo "function options_as_array() { " . "\n";
ob_start();
echo "\t\$defs = [];\n";
foreach ( $defs as $def ) {
    echo "\t\$defs['$def'] = $def;\n";
}
$contents = ob_get_contents();
ob_end_clean();
echo $contents;
echo "\n";
echo " return \$defs;" . "\n";
echo "}" . "\n";

$contents = ob_get_contents();
ob_end_clean();
file_put_contents(__DIR__ . '/generated_defines.php');
