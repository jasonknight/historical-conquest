<?php
/**
 * Plugin Name: Historical Conquest Game
 * Plugin URI: https://lycanthropenoir.com
 * Description: Custom Plugin for the Historical Conquest Game
 * Version: 1.0
 * Author: Jason Martion <contact@lycanthropenoir.com>
 * Author URI: https://app.codeable.io/tasks/new?preferredContractor=43500&ref=76T6q
 * License: Private
 */
namespace HistoricalConquest;
require_once(__DIR__ . '/core.php');
require_once(__DIR__ . '/settings.php');
require_once(__DIR__ . '/types.php');

function install_game_tables() {
    global $wpdb;
    $sql = render_template('installation.sql');
    $queries = explode(';',$sql);
    foreach ($queries as $q) {
        if ( empty(trim($q)) ) {
            continue;
        }
        $wpdb->query($q);
        echo $q . '<br />';
        if ( $wpdb->last_error) {
            echo "ERROR" . $wpdb->last_error . '<br />';
        }
    }
}
add_action('wp_enqueue_scripts',function () {
    wp_enqueue_script( 'jquery' );
});
function init() {
   add_action('template_redirect',__NAMESPACE__ . '\maybe_play_game'); 
   initialize_notices();
   if ( get('install-game-tables') ) {
       install_game_tables();
       exit; 
   }
   if ( get('import-cards-table') ) {
       import_cards_table();
       exit; 
   }
   
   if ( get('admin-editor')) {
       session_start();
       if ( get('deck') ) {
           $_SESSION['deck_filter'] = get('deck');
       }
       if ( get('subaction') == 'edit-card' && is_array(post('card')) ) {
            edit_card();
       }
   }
   add_shortcode('hcgame_card_ability_listing',__NAMESPACE__ . '\shortcode_hcgame_card_ability_listing');
   add_shortcode('hcgame_admin',__NAMESPACE__ . '\shortcode_hcgame_admin');
   add_shortcode('hcgame_admin_card_images',__NAMESPACE__ . '\shortcode_hcgame_admin_card_images');
   add_shortcode('hcgame_player_cp',__NAMESPACE__ . '\shortcode_hcgame_player_cp');
   if ( get('hcgame-rules-dl') ) {
        header("Content-Type: application/pdf");
        header("Content-Disposition:attachment;filename=historical-conquest-rules.pdf");
        echo file_get_contents(__DIR__ . '/assets/historical-conquest-rule-sheet.pdf');
        exit;
   }
   add_action('wp_ajax_save_deck',__NAMESPACE__ . '\ajax_save_deck');
   add_action('wp_ajax_get_decks',__NAMESPACE__ . '\ajax_get_decks');
   add_action('wp_ajax_get_games',__NAMESPACE__ . '\ajax_get_games');
   add_action('wp_ajax_decline_game',__NAMESPACE__ . '\ajax_decline_game');
   add_action('wp_ajax_accept_game',__NAMESPACE__ . '\ajax_accept_game');
   add_action('wp_ajax_get_deck_cards',__NAMESPACE__ . '\ajax_get_deck_cards');
   add_action('wp_ajax_get_player_cards',__NAMESPACE__ . '\ajax_get_player_cards');
   add_action('wp_ajax_create_deck',__NAMESPACE__ . '\ajax_create_deck');
   add_action('wp_ajax_create_challenge',__NAMESPACE__ . '\ajax_create_challenge');

   add_action('wp_ajax_play_card',__NAMESPACE__ . '\ajax_play_card');
   add_action('wp_ajax_get_board',__NAMESPACE__ . '\ajax_get_board');
   add_action('wp_ajax_cede_turn',__NAMESPACE__ . '\ajax_cede_turn');
   add_action('wp_ajax_draw_card',__NAMESPACE__ . '\ajax_draw_card');
}
function ajax_draw_card() {
    global $wpdb;
    $result = ['status' => 'KO','errors' => []];
    $game_id = post('game_id');
    $player_id = post('player_id');
    $uid = \get_current_user_id();
    $sql = "SELECT g.* FROM `hc_games` as g JOIN `hc_players` as p ON g.id = p.game_id WHERE g.id = %d AND p.id = %d AND p.user_id = %d";
    $sql = $wpdb->prepare($sql,$game_id,$player_id,$uid); 
    $game = $wpdb->get_row($sql);
    if ( !empty($wpdb->last_error) ) {
        $result['errors'][] = $sql;
        $result['errors'][] = $wpdb->last_error;
        send_json($result);
        exit;
    }
    // so we have the game, let's get the player
    $players = get_players($game->id,[$player_id],$result['errors']);
    if ( empty($players) ) {
        $result['errors'][] = "No players found";
        send_json($result);
        exit;
    }
    $p = $players[0];
    if ( $p->current_move + 1 > $p->max_moves ) {
        $result['errors'][] = "You are out of moves, you have made {$p->current_move} moves, and can only make {$p->max_moves}";
        send_json($result);
        exit;
    }
    if ( count($p->hand) > 4 ) {
        $result['errors'][] = "You can't have more than 5 cards in a hand.";
        send_json($result);
        exit;
    }
    $card = array_shift($p->draw_pile);
    action_log("Drew $card");
    $p->hand[] = $card;
    action_log("hand=" . join(',',$p->hand));
    $sql = "UPDATE `hc_players` SET hand = %s, drawpile = %s, current_move = current_move + 1 WHERE id = %d";
    $sql = $wpdb->prepare($sql,json_encode($p->hand),json_encode($p->draw_pile),$p->id);
    $wpdb->query($sql);
    if ( ! empty($wpdb->last_error) ) {
        $result['errors'][] = $sql;
        $result['errors'][] = $wpdb->last_error;
        send_json($result);
        exit;
    }
    send_json(get_game_board($game_id));
    exit;
}
function ajax_cede_turn() {
    $game_id = post('game_id');
    $result = ['status' => 'KO','errors' => []];
    next_player($game_id,$result['errors']);
    if ( empty($result['errors']) ) {
        $result = get_game_board($game_id); 
    }
    send_json($result);
    exit;
}
function ajax_get_board() {
   $game_id = post('game_id');
   $board = get_game_board($game_id);
   send_json($board);
   exit;
}
function ajax_play_card() {
   global $wpdb; 
   $result = [ 'status' => 'OK', 'errors' => [] ];
   $player_id = post('player_id');
   $game_id = post('game_id');
   $card_ext_id = post('card_ext_id');
   $row = post('row');
   $col = post('col');
   $errors = [];
   $players = get_players($game_id,[$player_id],$errors);
   if ( !empty($errors) ) {
        $result['status'] = 'KO';
        $result['errors'] = $errors;
        send_json($result);  
        exit;
   }
   if ( empty($players) ) {
        $errors[] = "There were no players found.";
        $result['status'] = 'KO';
        $result['errors'] = $errors;
        send_json($result);  
        exit;
   }
   foreach ($players as $player) {
       if ( $player->id == $player_id && \get_current_user_id() == $player->user_id ) {
            play_card($game_id,$player,$card_ext_id,$row,$col,$errors);
            if ( !empty($errors) ) {
                $result['status'] = 'KO';
                $result['errors'] = $errors;
                send_json($result);    
                exit;
            }
            send_json(get_game_board($game_id));
            exit;
       }
   }
    $errors[] = "Never found player to play card!";
    $result['status'] = 'KO';
    $result['errors'] = $errors;
    send_json($result);    
    exit;
}
function ajax_accept_game() {
    global $wpdb;
    $id = \get_current_user_id();
    $game_id = post('game');
    $deck_id = post('deck');
    // So we need to accept the challenge, and setup the game
    // We have to setup the piles, and create the data structures
    // necessary for generating the board when the player loads the game

    // step 1, get the game from the DB
    $sql = "SELECT g.id as gid,p.id as pid FROM `hc_games` as g JOIN `hc_players` as p ON p.game_id = g.id WHERE g.id = %d AND p.user_id = %d";
    $sql = $wpdb->prepare($sql,$game_id,$id);
    $game = $wpdb->get_row($sql);
    if ( empty($game) ) {
        send_json(['status' => 'KO', 'msg' => "That game was not found"]);
        exit;
    }
    // Note: player_id is the row id, not the user id in this context
    $player_id = $game->pid;
    // step 2 so we have the game, and the pid, 
    // TODO Validate the deck is playable?
    $sql = "UPDATE `hc_players` SET deck_id = %d WHERE id = %d";
    $sql = $wpdb->prepare($sql,$deck_id,$player_id);
    $wpdb->query($sql);
    if ( !empty($wpdb->last_error) ) {
        send_json(['status' => 'KO', 'msg' => $wpdb->last_error,'sql' => $sql]);
        exit;
    }
    // step 3, okay, we've set the deck id now let's get the players, and their cards for the deck
    // and create the piles and mats for this user,     
    $setup_player = function ($player_id,$game_id,$deck_id) use ($wpdb) {
        $sql = "
            SELECT cards.* 
            FROM `hc_player_decks_cards` dc 
            JOIN `hc_cards` as cards ON cards.id = dc.card_id 
            WHERE dc.deck_id = %d
        ";
        $sql = $wpdb->prepare($sql,$deck_id);
        $cards = $wpdb->get_results($sql);
        if ( !empty($wpdb->last_error) ) {
            send_json(['status' => 'KO', 'msg' => $wpdb->last_error,'sql' => $sql]);
            exit;
        }
        
        $draw_pile = [];
        $land_pile = [];
        $hand = [];
        foreach ( $cards as $c ) {
            if ( preg_match('/CARD_LAND/',type_to_name(intval($c->maintype))) ) {
                action_log("adding {$c->ext_id} to land pile");
                $land_pile[] = $c->ext_id;
            } else {
                action_log("adding {$c->ext_id} to draw pile");
                $draw_pile[] = $c->ext_id;
            }
        }
        shuffle($draw_pile);
        shuffle($land_pile);
        while (count($hand) < 5 ) {
            $hand[] = array_pop($draw_pile);
        }
        // Now that we have the land, and the draw pile, we need to update them
        $sql = "UPDATE `hc_players` SET drawpile = %s, landpile = %s, hand = %s WHERE id = %d";
        $sql = $wpdb->prepare($sql,json_encode($draw_pile),json_encode($land_pile),json_encode($hand),$player_id);
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            send_json(['status' => 'KO', 'msg' => $wpdb->last_error,'sql' => $sql]);
            exit;
        }
        // So we've setup the piles, now we need to setup the mats
        $mat = get_base_table();
        $updates = [];
        foreach ( ['playmat','abilitymat','damagemat'] as $mname ) {
           $updates[] = $wpdb->prepare("`$mname` = %s",json_encode($mat));
        }
        $sql = "UPDATE `hc_players` SET ".join(',',$updates)." WHERE id = %d";
        $sql = $wpdb->prepare($sql,$player_id);
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            send_json(['status' => 'KO', 'msg' => $wpdb->last_error,'sql' => $sql]);
            exit;
        }
    };// end of setup_player
    $players = $wpdb->get_results($wpdb->prepare("SELECT * FROM `hc_players` WHERE game_id = %d",$game_id));
    foreach ( $players as $player ) {
        $setup_player($player->id,$game_id,$player->deck_id);
    }
    // Now that we've setup the piles, time to activate the game
    $sql = "UPDATE `hc_games` SET active = 1 WHERE id = %d";
    $sql = $wpdb->prepare($sql,$game_id);
    $wpdb->query($sql);
    if ( !empty($wpdb->last_error) ) {
        send_json(['status' => 'KO', 'msg' => $wpdb->last_error,'sql' => $sql]);
        exit;
    }
    // Decide who goes first
    $sql = "UPDATE `hc_games` SET current_player_id = (SELECT id FROM `hc_players` WHERE game_id = %d ORDER BY RAND() LIMIT 1)  WHERE id = %d";
    $sql = $wpdb->prepare($sql,$game_id,$game_id);
    $wpdb->query($sql);
    if ( !empty($wpdb->last_error) ) {
        send_json(['status' => 'KO', 'msg' => $wpdb->last_error,'sql' => $sql]);
        exit;
    }
    send_json(['status' => 'OK']);
    exit;
}
function ajax_decline_game() {
    global $wpdb;
    $id = \get_current_user_id();
    $game_id = post('game');
    list($my_games,$others_games) = _get_games($id); 
    $status = "KO";
    $msg = "Game not found";
    foreach ( $others_games as $g) {
        if ( $g->id == intval($game_id) ) {
            $wpdb->query($wpdb->prepare("UPDATE `hc_games` SET declined = 1 WHERE id = %d",$game_id));
            $status = "OK";
            $msg = "Game Declined";
        }
    }
    send_json(['status' => $status, 'msg' => $msg]);
    exit;
}
function ajax_create_challenge() {
    global $wpdb;
    $msgs = [];
    $id = \get_current_user_id();
    $opponent = post('opponent');
    $deck_id = post('deck');
    $create_sql = "INSERT INTO `hc_games` (created_at,active,created_by) VALUES (NOW(),0,%d)"; 
    $create_sql = $wpdb->prepare($create_sql,$id);
    $wpdb->query($create_sql);
    $msgs[] = $wpdb->last_error;
    $game_id = $wpdb->get_var("SELECT LAST_INSERT_ID();");
    $p1_sql = "INSERT INTO `hc_players` (game_id,user_id,name,morale,age,deck_id) VALUES (%d,%d,%s,0,0,%d)";
    $p1_sql = $wpdb->prepare($p1_sql,$game_id,$id,_display_name($id),$deck_id);
    $wpdb->query($p1_sql);
    $msgs[] = $wpdb->last_error;

    $p2_sql = "INSERT INTO `hc_players` (game_id,user_id,name,morale,age,deck_id) VALUES (%d,%d,%s,0,0,%d)";
    $p2_sql = $wpdb->prepare($p2_sql,$game_id,$opponent,_display_name($opponent),0);
    $wpdb->query($p2_sql);
    $msgs[] = $wpdb->last_error;

    send_json(['status' => 'OK','msg' => $msgs]);
    exit;
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
function ajax_get_games() {
    global $wpdb;
    $id = \get_current_user_id();
    list($my_games,$others_games) = _get_games($id); 
    send_json(['status' => 'OK', 'my_games' => $my_games, 'others_games' => $others_games]);
    exit;
}
function ajax_save_deck() {
    global $wpdb;
    $deck_id = post('deck_id');
    $card_ids = array_unique(post('card_ids'));
    $card_count = count($card_ids);
    $uid = \get_current_user_id();
    $decks = $wpdb->get_results(
        $wpdb->prepare("
            SELECT * FROM `hc_player_decks` where player_id = %d AND id = %d
        ",$uid,$deck_id) 
    );
    if ( empty($decks) ) {
        send_json(['status' => 'KO', 'msg' => "That deck does not exist"]);
        exit;
    }
    $wpdb->query( 
        $wpdb->prepare("DELETE FROM `hc_player_decks_cards` WHERE deck_id = %d",$deck_id));
    $card_ids = join(',',array_map(function ($cid) {
        global $wpdb;
        return $wpdb->prepare('%s',$cid);
    },$card_ids));

    $sql = $wpdb->prepare("INSERT INTO `hc_player_decks_cards` (deck_id,card_id,ext_id) SELECT %d as deck_id,id as card_id, ext_id FROM `hc_cards` WHERE ext_id IN ($card_ids)",$deck_id);
    $wpdb->query($sql);
    $wpdb->query(
        $wpdb->prepare("UPDATE `hc_player_decks` SET card_count = %d WHERE id = %d",$card_count,$deck_id));
    send_json(['status' => 'OK', 'msg' => $wpdb->last_error, 'sql' => $sql]);
    exit;
}
function ajax_get_player_cards() {
    global $wpdb;
    $id = \get_current_user_id();
    $deck_name = post('deck_name');
    $sql = $wpdb->prepare("
        SELECT * FROM `hc_cards` as cards JOIN hc_player_cards as pcards ON pcards.card_id = cards.id WHERE pcards.player_id = %d
    ",$id);
    $cards = $wpdb->get_results($sql,ARRAY_A);
    if ( empty($cards) )
        $cards = [];
    send_json($cards);
    exit();
}
function ajax_create_deck() {
    global $wpdb;
    $id = \get_current_user_id();
    $deck_name = post('deck_name');
    $sql = $wpdb->prepare("
        SELECT * FROM `hc_player_decks` WHERE player_id = %d
    ",$id);
    $decks = $wpdb->get_results($sql,ARRAY_A);
    if ( empty($decks) ) {
        $decks = [];
    }
    foreach ( $decks as $deck ) {
        if ( $deck['name'] == $deck_name ) {
            send_json(['status' => 'KO', 'msg' => "You already have a deck with that name"]);
            exit();
        }
    }
    $sql = $wpdb->prepare("INSERT INTO `hc_player_decks` (player_id, name) VALUES (%d,%s)",$id,$deck_name);
    $wpdb->query($sql);
    send_json([
        'status' => 'OK',
        'msg' => $wpdb->last_error, 
    ]);
    exit();
}
function ajax_get_decks() {
    global $wpdb;
    $id = \get_current_user_id();
    $sql = $wpdb->prepare("
        SELECT * FROM `hc_player_decks` WHERE player_id = %d
    ",$id);
    $decks = $wpdb->get_results($sql,ARRAY_A);
    if ( empty($decks) ) {
        $decks = [];
    }
    send_json($decks);
    exit;
}
function ajax_get_deck_cards() {
    global $wpdb;
    $id = \get_current_user_id();
    $deck_id = post('deck_id');
    if ( empty($wpdb->get_results($wpdb->prepare("SELECT * FROM `hc_player_decks` WHERE player_id = %d AND id = %d",$id,$deck_id)))) {
        send_json(["status" => "KO", "msg" => "That deck does not belong to you."]);
        exit;
    }
    $sql = $wpdb->prepare("
        SELECT * FROM `hc_player_decks_cards` WHERE deck_id = %d
    ",$deck_id);
    $cards = $wpdb->get_results($sql,ARRAY_A);
    if ( empty($cards) )
        $cards = [];
    send_json($cards);
    exit;
}
function check_player_has_cards($id) {
    global $wpdb;
    $res = $wpdb->get_results(
            $wpdb->prepare("SELECT 1 FROM `hc_player_cards` WHERE player_id = %d",$id));
    return !empty($res);
}
function assign_basic_cards_to_player($id) {
    global $wpdb;
    $sql = $wpdb->prepare(
        "INSERT INTO `hc_player_cards` 
            (SELECT %d as player_id, id as card_id, ext_id FROM `hc_cards` WHERE deck IN ('B','C'))",$id);
    $wpdb->query($sql); 
    if ( $wpdb->last_error ) {
        die($wpdb->last_error);
    }
}
function shortcode_hcgame_player_cp($attrs) {
    global $wpdb;
    // So we need to make sure the user has the base cards
    if ( !check_player_has_cards(\get_current_user_id()) ) {
        assign_basic_cards_to_player(\get_current_user_id());
    }
    $sql = "
        SELECT cards.id,cards.ext_id,cards.name FROM `hc_player_cards` as pcards 
        JOIN `hc_cards` as cards on cards.id = pcards.card_id
        WHERE
            pcards.player_id = %d
    ";
    $sql = $wpdb->prepare($sql,\get_current_user_id());
    $owned_cards = $wpdb->get_results($sql,ARRAY_A);
    echo render_template('player-control-panel.php',['owned_cards' => $owned_cards]);
}
function shortcode_hcgame_card_ability_listing($attrs) {
    $cards = get_cards_without_abilities();
    echo render_template('card-ability-listing.php', ['cards' => $cards ]);
}
function upload_attachment($file,$file_key) {
    //dbg_notice(__FUNCTION__ . ": file_key=$file_key");
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    $user_id = \get_current_user_id();
    $errors = $file['error'];
    $upload_dir = \wp_upload_dir();
    $final_image_url = '';
    if ( ! is_array($errors) )
        $errors = [$errors];
    foreach (  $errors as $key => $error ) {
        if ( $error == UPLOAD_ERR_OK ) {
            $ufile = \wp_handle_upload($file,['test_form' => false]);
            $type = $file['type'];
            $name = $file['name'];
            $name_parts = pathinfo($name);
            $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
            $url = $ufile['url'];
            $file = $ufile['file'];
            $title = $name;
            // Build Attachment
            $attachment = array(
              'guid'           => $url,
              'post_mime_type' => $type,
              'post_title'     => $title,
              'post_content'   => "",
              'post_author' => \get_current_user_id()
            );
            // This should never be set as it would then overwrite an existing attachment
            if(isset($attachment['ID'])) {
              unset($attachment['ID']);
            }
            // Save the attachment metadata
            $attachment_id = \wp_insert_attachment($attachment, $file);

            // Update user avatar
            if(!is_wp_error($attachment_id)) {
                \wp_generate_attachment_metadata($attachment_id,$file);
                return $attachment_id; 
            }
        }
    }
    return null;
}
function shortcode_hcgame_admin_card_images($attrs) {
    global $wpdb;
    if ( files('illustration') ) {
        $aid = upload_attachment(files('illustration'),'none');
        if ( $aid ) {
            \update_post_meta($aid,'_card_ext',post('card_ext'));
            \update_post_meta($aid,'_card_id',post('card_id'));
            $sql = $wpdb->prepare("UPDATE `hc_cards` SET illustration = %d WHERE id = %d",$aid,post('card_id')); 
            $wpdb->query($sql);
        }
    }
    if ( files('background') ) {
        $aid = upload_attachment(files('background'),'none');
        if ( $aid ) {
            \update_post_meta($aid,'_card_ext',post('card_ext'));
            \update_post_meta($aid,'_card_id',post('card_id'));
            $sql = $wpdb->prepare("UPDATE `hc_cards` SET background_image = %d WHERE id = %d",$aid,post('card_id')); 
            $wpdb->query($sql);
        }
    }
    echo render_template('admin-editor/card-images.php');
}

function shortcode_hcgame_admin($attrs) {
    session_start();
    if ( get('deck') ) {
       $_SESSION['deck_filter'] = get('deck');
    }
    if ( get('subaction') == 'edit-card' && is_array(post('card')) ) {
        edit_card();
    }
    echo render_template('admin_editor.php');
}
function initialize_notices() {
    update_option('hcnotices',[]);
}
function splat_and_die($ar) {
    echo "<pre>".print_r($ar,true)."</pre>". PHP_EOL;
    die(__FUNCTION__);
}
function edit_card() {
    global $wpdb;
    $card = post('card');
    $abilities = post('abilities');
    if ( $card['id'] == '0' ) {
        create_new_card($card,$abilities);
        return;
    } 
    $updates = [];
    $card['updated_at'] = date('Y-m-d H:i:s');
    foreach ( $card as $key=>$value ) {
        if ( $key == 'id' )
            continue;
        if ( in_array($key,['ethnicity','maintype','continent','religion','climate']) ) {
            if ( defined($value) ) {
                $value = constant($value);
            }
        }
        $updates[] = $wpdb->prepare("`$key` = %s",stripslashes($value));
    }
    $sql = "UPDATE `hc_cards` SET " . join(',',$updates) . " WHERE `id` = {$card['id']}";
    $wpdb->query($sql); 
    if ( $wpdb->last_error) {
        add_notice('error',$wpdb->last_error);
    }
    if ( !is_array($abilities) ) {
        $abilities = [];
    }
    $wpdb->query( $wpdb->prepare("DELETE FROM `hc_card_abilities` WHERE card_id = %d",$card['id']) );
    foreach ( $abilities as $ability ) {
        create_new_ability($card,$ability);
    }
}
function create_new_card($card,$abilities) {

}
function create_new_ability($card,$ability) {
    global $wpdb;
    if ( empty($ability['description']) )
        return;
    $keys = [];
    $values = [];
    $ability['created_at'] = date('Y-m-d H:i:s');
    foreach ( ['ability_type','usage_type','apply_to_type','apply_to_scope'] as $def ) {
        if ( !isset($ability[$def]) )
            continue;
        if ( defined($ability[$def]) ) {
            $ability[$def] = constant($ability[$def]);
        } else {
            print_r($ability);
            die("$def {$ability[$def]} is not defined?");
        }
    }   
    $ability['card_id'] = $card['id'];
    foreach ( $ability as $key=>$value ) {
        if ( $key == 'id' )
            continue;
        $keys[] = "`$key`";
        if ( $key == 'card_id' ) {
            $values[] = $wpdb->prepare('%s',$card['id']);
        } else {
            $values[] = $wpdb->prepare('%s',$value);
        }
    }
    $sql = "INSERT INTO `hc_card_abilities` (" . join(',',$keys) . ") VALUES (" . join(',',$values) . ")";
    $wpdb->query($sql);
    if ( $wpdb->last_error) {
       add_notice('error',$wpdb->last_error); 
    } 
}
function import_cards_table() {
    global $wpdb;
    include __DIR__ . '/tools/convert_html.php';
    $keys = array_keys($insertable_entries[0]);
    foreach ( $keys as &$key ) {
        $key = "`$key`";
    }
    $final_values = [];
    $lc = 0;
    foreach ( $insertable_entries as $i ) {
        if ( defined($i['maintype']) ) {
            $i['maintype'] = constant($i['maintype']);
        }
        if ( defined($i['continent']) ) {
            $i['continent'] = constant($i['continent']);
        }
        $values = array_values($i);
        foreach ( $values as &$value ) {
            if ( is_numeric($value) ) {
                $value = $wpdb->prepare('%d',$value);
            } else {
                $value = $wpdb->prepare('%s',$value);
            }
        }
        if ( count($values) != count($keys) ) {
            print_r($keys);
            print_r($i);
            echo "Count mismatch" . PHP_EOL;
            exit;
        }
        $final_values[] = '(' . join(',',$values) . ')'; 
    }
    $sql = "TRUNCATE `hc_cards`;";
    $wpdb->query($sql);
    $sql = "INSERT INTO `hc_cards` (" . join(',',$keys) . ') VALUES ' . join(',',$final_values) . ";";
    $wpdb->query($sql);
    echo $wpdb->last_error;
}
function maybe_play_game() {
    if ( get('action') == 'historical-conquest-game' ) {
        if ( get('admin-editor') ) {
            echo render_template('admin_editor.php');
        } else {
            echo render_template('game.php');
        }
        exit;
    }
}
require_once(__DIR__ . '/admin.php');
\add_action('init', __NAMESPACE__ . '\init');

