<?php
namespace HistoricalConquest;
function ajax_init() {
    add_action('wp_ajax_save_deck',__NAMESPACE__ . '\ajax_save_deck');
    add_action('wp_ajax_nopriv_save_deck',__NAMESPACE__ . '\ajax_save_deck');

    add_action('wp_ajax_get_decks',__NAMESPACE__ . '\ajax_get_decks');
    add_action('wp_ajax_nopriv_get_decks',__NAMESPACE__ . '\ajax_get_decks');

    add_action('wp_ajax_get_games',__NAMESPACE__ . '\ajax_get_games');
    add_action('wp_ajax_nopriv_get_games',__NAMESPACE__ . '\ajax_get_games');

    add_action('wp_ajax_decline_game',__NAMESPACE__ . '\ajax_decline_game');
    add_action('wp_ajax_nopriv_decline_game',__NAMESPACE__ . '\ajax_decline_game');

    add_action('wp_ajax_accept_game',__NAMESPACE__ . '\ajax_accept_game');
    add_action('wp_ajax_nopriv_accept_game',__NAMESPACE__ . '\ajax_accept_game');
    
    add_action('wp_ajax_get_deck_cards',__NAMESPACE__ . '\ajax_get_deck_cards');
    add_action('wp_ajax_nopriv_get_deck_cards',__NAMESPACE__ . '\ajax_get_deck_cards');

    add_action('wp_ajax_get_player_cards',__NAMESPACE__ . '\ajax_get_player_cards');
    add_action('wp_ajax_nopriv_get_player_cards',__NAMESPACE__ . '\ajax_get_player_cards');

    add_action('wp_ajax_create_deck',__NAMESPACE__ . '\ajax_create_deck');
    add_action('wp_ajax_nopriv_create_deck',__NAMESPACE__ . '\ajax_create_deck');

    add_action('wp_ajax_delete_deck',__NAMESPACE__ . '\ajax_delete_deck');
    add_action('wp_ajax_nopriv_delete_deck',__NAMESPACE__ . '\ajax_delete_deck');

    add_action('wp_ajax_create_challenge',__NAMESPACE__ . '\ajax_create_challenge');
    add_action('wp_ajax_nopriv_create_challenge',__NAMESPACE__ . '\ajax_create_challenge');

    add_action('wp_ajax_play_card',__NAMESPACE__ . '\ajax_play_card');
    add_action('wp_ajax_nopriv_play_card',__NAMESPACE__ . '\ajax_play_card');

    add_action('wp_ajax_get_board',__NAMESPACE__ . '\ajax_get_board');
    add_action('wp_ajax_nopriv_get_board',__NAMESPACE__ . '\ajax_get_board');

    add_action('wp_ajax_get_current_player',__NAMESPACE__ . '\ajax_get_current_player');
    add_action('wp_ajax_nopriv_get_current_player',__NAMESPACE__ . '\ajax_get_current_player');


    add_action('wp_ajax_cede_turn',__NAMESPACE__ . '\ajax_cede_turn');
    add_action('wp_ajax_nopriv_cede_turn',__NAMESPACE__ . '\ajax_cede_turn');

    add_action('wp_ajax_draw_card',__NAMESPACE__ . '\ajax_draw_card');
    add_action('wp_ajax_nopriv_draw_card',__NAMESPACE__ . '\ajax_draw_card');

    add_action('wp_ajax_discard',__NAMESPACE__ . '\ajax_discard');
    add_action('wp_ajax_nopriv_discard',__NAMESPACE__ . '\ajax_discard');


    add_action('wp_ajax_attack_player',__NAMESPACE__ . '\ajax_attack_player');
    add_action('wp_ajax_nopriv_attack_player',__NAMESPACE__ . '\ajax_attack_player');
}
function ajax_get_current_player() {
    global $wpdb;
    apply_fuser();
    $game_id = post('game_id');
    $pid = post('player_id');
    $errors = [];
    $result = [
        'status' => 'OK',
        'player' => null,
        'errors' => [],
    ];
    $players = get_players($game_id,[$pid],$errors);
    if ( empty($players) ) {
        $result['status'] = 'KO';
        $result['errors'] = $errors;
        send_json($result);
        exit;
    } 
    $result['player'] = $players[0];
    send_json($result);
    exit;
}
function ajax_attack_player() {
    global $wpdb;
    $errors = [];
    $messages = [];
    $game_id = post('game_id');
    if ( is_over($game_id) ) {
        $result = [];
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
    }
    $attacker_id = post('attacker');
    $defender_id = post('defender');
    $src_ext_id = post('src_ext_id');
    $attacker_land_ext_id = post('attacker_land_ext_id');
    $defender_land_ext_id = post('defender_land_ext_id');
    $result = attack_player(
        $game_id,
        $attacker_id,
        $defender_id,
        $src_ext_id,
        $attacker_land_ext_id,
        $defender_land_ext_id,
        $messages,
        $errors
    );
    $result['errors'] = $errors;
    $result['messages'] = $messages;
    $board = get_game_board($game_id);
    $result['new_board'] = $board;
    send_json($result);
    exit;
}
function ajax_discard() {
    global $wpdb;
    apply_fuser();
    $result = ['status' => 'KO','errors' => []];
    $game_id = post('game_id');
    if ( is_over($game_id) ) {
        $result = [];
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
    }
    $player_id = post('player_id');
    $uid = \get_current_user_id();
    $ext_id = post('ext_id');
    $row = post('row');
    $col = post('col');
    $hint = post('hint');
    discard($game_id,$player_id,$uid,$ext_id,$row,$col,$hint,$errors);
    if ( ! empty($errors) ) {
        $result['errors'] = $errors;
        send_json($result);
        exit;
    }
    $players = get_players($game_id,[$player_id],$errors);
    if ( ! empty($errors) ) {
        $result['errors'] = $errors;
        send_json($result);
        exit;
    }
    $result['status'] = 'OK';
    $result['player'] = $players[0];
    send_json($result);
    exit;
}
function ajax_draw_card() {
    global $wpdb;
    apply_fuser();
    $result = ['status' => 'KO','errors' => []];
    $game_id = post('game_id');
    action_log(__FUNCTION__ . " beginning draw_card");
    if ( is_over($game_id) ) {
        $result = [];
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
    }
    
    $player_id = post('player_id');
    $uid = \get_current_user_id();
    action_log(__FUNCTION__ . "pid=$player_id,uid=$uid");
    draw_card($game_id,$player_id,$uid,$errors);
    if ( ! empty($errors) ) {
        $result['errors'] = $errors;
        send_json($result);
        exit;
    }
    send_json(get_game_board($game_id));
    exit;
}
function ajax_cede_turn() {
    apply_fuser();
    $uid = \get_current_user_id();
    $game_id = post('game_id');
    if ( is_over($game_id) ) {
        $result = [];
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
    }
    $pid = post('player_id');
    $result = ['status' => 'KO','errors' => []];
    if ( ! can_cede_turn($game_id,$pid,$uid) ) {
        $result['errors'][] = MSG_YOU_CANT_DO_THAT; 
        send_json($result);
        exit;
    }
    next_player($game_id,$result['errors']);
    if ( empty($result['errors']) ) {
        $result = get_game_board($game_id); 
    }
    send_json($result);
    exit;
}
function ajax_get_board() {
    apply_fuser();
    $game_id = post('game_id');
    $board = get_game_board($game_id);
    send_json($board);
    exit;
}
function ajax_play_card() {
   global $wpdb; 
   apply_fuser();
   $result = [ 'status' => 'OK', 'errors' => [] ];
   $player_id = post('player_id');
   $game_id = post('game_id');
   if ( is_over($game_id) ) {
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
   }
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

function accept_game($game_id,$deck_id,$id,&$errors) {
    global $wpdb;
    // So we need to accept the challenge, and setup the game
    // We have to setup the piles, and create the data structures
    // necessary for generating the board when the player loads the game

    // step 1, get the game from the DB
    $sql = "SELECT g.id as gid,p.id as pid FROM `hc_games` as g JOIN `hc_players` as p ON p.game_id = g.id WHERE g.id = %d AND p.user_id = %d";
    $sql = $wpdb->prepare($sql,$game_id,$id);
    $game = $wpdb->get_row($sql);
    if ( empty($game) ) {
        $errors[] = "That game was not found";
        return;
    }
    // Note: player_id is the row id, not the user id in this context
    $player_id = $game->pid;
    // step 2 so we have the game, and the pid, 
    // TODO Validate the deck is playable?
    $sql = "UPDATE `hc_players` SET deck_id = %d WHERE id = %d";
    $sql = $wpdb->prepare($sql,$deck_id,$player_id);
    $wpdb->query($sql);
    if ( !empty($wpdb->last_error) ) {
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
        return;
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
            $errors[] = $sql;
            $errors[] = $wpdb->last_error;
            return;
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
        $updates = [];
        $updates[] = $wpdb->prepare("drawpile = %s",json_encode($draw_pile));
        $updates[] = $wpdb->prepare("landpile = %s",json_encode($land_pile));
        $updates[] = $wpdb->prepare("hand = %s",json_encode($hand));
        // So we've setup the piles, now we need to setup the mats
        $mat = get_base_table();
        foreach ( ['playmat','abilitymat','damagemat'] as $mname ) {
           $updates[] = $wpdb->prepare("`$mname` = %s",json_encode($mat));
        }
        $updates[] = "attacks = max_attacks";
        $updates[] = "can_attack = 0";
        $sql = "UPDATE `hc_players` SET ".join(',',$updates)." WHERE id = %d";
        $sql = $wpdb->prepare($sql,$player_id);
        $wpdb->query($sql);
        if ( !empty($wpdb->last_error) ) {
            $errors[] = $sql;
            $errors[] = $wpdb->last_error;
            return;
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
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
        return;
    }
    // Decide who goes first
    $sql = "UPDATE `hc_games` SET current_player_id = (SELECT id FROM `hc_players` WHERE game_id = %d ORDER BY RAND() LIMIT 1)  WHERE id = %d";
    $sql = $wpdb->prepare($sql,$game_id,$game_id);
    $wpdb->query($sql);
    if ( !empty($wpdb->last_error) ) {
        $errors[] = $sql;
        $errors[] = $wpdb->last_error;
        return;
    } 
}
function ajax_accept_game() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $game_id = post('game');
    if ( is_over($game_id) ) {
        $result = [];
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
    }
    $deck_id = _get_deck_id();
    if ( !owns_deck($id,$deck_id) ) {
        send_json(['status' => 'KO', 'msg' => MSG_BAD_DECK_OWNER, 'errors' => $errors]);
        exit;
    }
    $errors = [];
    accept_game($game_id,$deck_id,$id,$errors); 
    if ( ! empty($errors) ) {
        send_json(['status' => 'KO', 'errors' => $errors]);
    }
    send_json(['status' => 'OK', 'errors' => []]);
    exit;
}
function ajax_decline_game() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $game_id = post('game');
    if ( is_over($game_id) ) {
        $result = [];
        $result['status'] = 'KO';
        $result['errors'][] = MSG_GAME_OVER;
        send_json($result);
        exit;
    }
    list($my_games,$others_games) = _get_games($id); 
    $status = "KO";
    $msg = MSG_GAME_NOT_FOUND;
    foreach ( $others_games as $g) {
        if ( $g->id == intval($game_id) ) {
            $sql = $wpdb->prepare("UPDATE `hc_games` SET declined = 1 WHERE id = %d",$game_id);
            action_log($sql);
            $wpdb->query($sql);
            $status = "OK";
            $msg = MSG_GAME_DECLINED;
        }
    }
    list($my_games,$others_games) = _get_games($id); 
    send_json(['status' => $status, 'msg' => $msg, 'games' => ['my_games' => $my_games,'others_games' => $others_games]]);
    exit;
}
function _get_deck_id() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $deck_id = '';
    if ( post('deck_id') ) {
        $deck_id = post('deck_id');
    } else {
        $deck_id = post('deck');
    }
    action_log(__FUNCTION__ . " so far deck_id=$deck_id");
    if ( empty($deck_id) && !empty(post('deck_name')) ) {
        $sql = $wpdb->prepare(
                "SELECT id FROM `hc_player_decks` WHERE player_id = $id AND name = %s", post('deck_name'));
        action_log($sql);
        $deck_id = $wpdb->get_var(
            $sql
        );
        action_log(__FUNCTION__ . " deck_id=$deck_id");
    } else if ( empty($deck_id) ) {
        action_log(__FUNCTION__ . " deck_id is empty?");
        send_json(['status' => 'KO', 'msg' => MSG_BAD_DECK]);
        exit;
    }
    return $deck_id;
}
function ajax_create_challenge() {
    global $wpdb;
    apply_fuser();
    $msgs = [];
    $id = \get_current_user_id();
    $opponent = post('opponent');
    $deck_id = _get_deck_id(); 
    if ( owns_deck($id,$deck_id) === false ) {
        send_json(['status' => 'KO', 'msg' => MSG_BAD_DECK_OWNER]);
        exit;
    }
    if ( !user_id_exists($opponent) ) {
        send_json(['status' => 'KO', 'msg' => MSG_BAD_OPPONENT]);
        exit;
    }    
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

    send_json(['status' => 'OK','msg' => $msgs, 'game_id' => $game_id]);
    exit;
}
function ajax_get_games() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    list($my_games,$others_games) = _get_games($id); 
    $my_games = array_values(array_filter($my_games,function ($g) { return intval($g->winner_id) === 0; }));
    $others_games = array_values(array_filter($others_games,function ($g) { return intval($g->winner_id) === 0; }));
    send_json(['status' => 'OK', 'my_games' => $my_games, 'others_games' => $others_games]);
    exit;
}

function ajax_get_player_cards() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $sql = $wpdb->prepare("
        SELECT * FROM `hc_cards` as cards JOIN hc_player_cards as pcards ON pcards.card_id = cards.id WHERE pcards.player_id = %d
    ",$id);
    $cards = $wpdb->get_results($sql,ARRAY_A);
    if ( empty($cards) )
        $cards = [];
    send_json(['status' => 'OK', 'cards' => $cards]);
    exit();
}
function ajax_save_deck() {
    global $wpdb;
    apply_fuser();
    $deck_id = _get_deck_id(); 
    $id = \get_current_user_id();
    $card_ids = array_unique(post('card_ids'));
    $card_count = count($card_ids);
    if ( $card_count === 0 ) {
        send_json(['status' => 'KO', 'msg' => "There are no cards to insert!"]);
    }
    $uid = \get_current_user_id();
    $sql = $wpdb->prepare("
            SELECT * FROM `hc_player_decks` where player_id = %d AND id = %d
    ",$uid,$deck_id);
    $decks = $wpdb->get_results(
        $sql      
    );
    action_log($sql);
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

    $sql = "INSERT INTO `hc_player_decks_cards` (deck_id,card_id,ext_id) SELECT %d as deck_id,id as card_id, ext_id FROM `hc_cards` WHERE ext_id IN ($card_ids)";
    $sql = $wpdb->prepare($sql,$deck_id);
    $wpdb->query($sql);
    $wpdb->query(
        $wpdb->prepare("UPDATE `hc_player_decks` SET card_count = %d WHERE id = %d",$card_count,$deck_id));
    send_json(['status' => 'OK', 'msg' => $wpdb->last_error, 'sql' => $sql]);
    exit;
}
function ajax_delete_deck() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $deck_id = _get_deck_id();
    $sql = $wpdb->prepare(
        "DELETE FROM `hc_player_decks` WHERE player_id = %d AND id = %d",
        $id,
        $deck_id 
    );
    $wpdb->query($sql);
    $sql = $wpdb->prepare(
        "DELETE FROM `hc_player_decks_cards` WHERE deck_id = %d",
        $deck_id 
    );
    $wpdb->query($sql);
    ajax_get_decks();
}
function ajax_create_deck() {
    global $wpdb;
    apply_fuser();
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
        'decks' => get_player_decks($id),
    ]);
    exit();
}
function ajax_get_decks() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $decks = get_player_decks($id); 
    send_json(['status' => 'OK', 'decks' => $decks, 'for_user' => $id]);
    exit;
}
function ajax_get_deck_cards() {
    global $wpdb;
    apply_fuser();
    $id = \get_current_user_id();
    $deck_id = _get_deck_id();
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
    send_json(['status' => 'OK', 'cards' => $cards]);
    exit;
}
