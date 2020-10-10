<?php
namespace HistoricalConquest;
function ajax_init() {
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
    send_json(['status' => 'OK', 'cards' => $cards]);
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
    send_json(['status' => 'OK', 'decks' => $decks]);
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
    send_json(['status' => 'OK', 'cards' => $cards]);
    exit;
}
