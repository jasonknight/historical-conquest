<?php
namespace HistoricalConquest;
include dirname(__DIR__) . "/settings.php";
$s = settings();
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
ob_start();
echo "<?php\nnamespace HistoricalConquest;\n";
$iota = array_to_defines($s->card_types,0,'CARD_');
$iota = array_to_defines($s->apply_to_scope_types,$iota,'SCOPE_');
$iota = array_to_defines($s->apply_to_types,$iota,'APPLY_');
$iota = array_to_defines($s->usage_types,$iota,'USAGE_');
$iota = array_to_defines($s->ability_types,$iota,'ABILITY_');
$iota = array_to_defines($s->card_religions,$iota,'RELIGION_');
$iota = array_to_defines($s->card_continents,$iota,'CONTINENT_');
$iota = array_to_defines($s->card_climates,$iota,'CLIMATE_');
$iota = array_to_defines($s->ethnicities,$iota,'ETH_');
$defs_by_prefix = [];
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
file_put_contents(dirname(__DIR__) . '/types.php',$contents);
