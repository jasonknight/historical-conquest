<?php
namespace HistoricalConquest;
// Here we will need to simulate a full series from
// start to finish.
//
// 1: We need to create decks to use for each player.
// 2: P1 challenges P2
// 3: P2 Accepts P1
// 4: P1 goes first, plays 3 cards
// 5: P2 goes second, plays 3 cards
$u1 = \get_user_by('ID',1);
$u2 = \get_user_by('ID',2);

$users = [$u1,$u2];
// Need to create a deck for each user
function _get_ext_ids_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",trim($l)); },$lines);
    test(!empty($lines),"loading player board $fname");
    $cards = [];
    for ( $row = 1; $row < 15; $row++ ) {
        for ( $col = 0; $col < 10; $col++ ) {
            if ( !empty(trim($lines[$row][$col])) ) {
                $cards[] = $lines[$row][$col];
            }
        }
    }
    return $cards;
}
function _get_cards_from_board($name) {
    global $wpdb;
    $cards = _get_ext_ids_from_board($name); 
    $cards = array_map(function ($c) { 
        global $wpdb;
        return $wpdb->prepare('%s',$c);
    },$cards);
    $sql = "SELECT * FROM `hc_cards` WHERE `ext_id` in (".join(',',$cards).")";
    $cards = $wpdb->get_results($sql,ARRAY_A);
    test(!empty($cards), "loading cards from board: $name");
    return $cards;
}
foreach ( $users as $user ) {
    $dname = "U{$user->ID}DECK";
    $data = [
        'action' => 'delete_deck',
        '_fuser' => $user->ID,
        'deck_name' => $dname, 
    ];
    $resp = ajax_post($data);
    test($resp->body['status'] == 'OK',__FILE__ . " deck $dname deletion");
    $data = [
        'action' => 'create_deck',
        '_fuser' => $user->ID,
        'deck_name' => $dname, 
    ];
    $resp = ajax_post($data);
    test($resp->body['status'] == 'OK',__FILE__ . " deck $dname creation");
    // So we just inject the cards into the player if they aren't there
    $cards = _get_cards_from_board('AttackBoard_1_player_a.csv');
    foreach ($cards as $card) {
        if ( 
            empty(
                $wpdb->get_results(
                    "SELECT 1 FROM `hc_player_cards` WHERE player_id = {$user->ID} AND card_id = {$card['id']}") ) 
        ) {
            $sql = "INSERT INTO `hc_player_cards` (player_id,card_id) VALUES ({$user->ID},{$card['id']})";
            $wpdb->query($sql);
            test(empty($wpdb->last_error),"Inserting cards: {$wpdb->last_error}");
        }
    }
    // Now we need to add the cards to the deck
    $ext_ids = array_map(function ($card) {
        return $card['ext_id']; 
    },$cards);
    $data = ['action' => 'save_deck', '_fuser' => $user->ID, 'deck_name' => $dname, 'card_ids' => $ext_ids];
    $resp = ajax_post($data);
    test($resp->body['status'] === 'OK', "saved $dname");
}
///////////////////////////////////////////////////////
//              Now u1 needs to challenge u2         //
///////////////////////////////////////////////////////

// Let's test the failures
// What if we give a weird opponent?
$data = ['action' => 'create_challenge', '_fuser' => $u1->ID, 'deck_name' => "U{$u1->ID}DECK", 'opponent' => 0];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "bad opponent");
test($resp->body['msg'] === MSG_BAD_OPPONENT,"Received MSG_BAD_OPPONENT");
// What if we don't send a deck?
$data = ['action' => 'create_challenge', '_fuser' => $u1->ID, 'opponent' => $u2->ID];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "no deck sent");
test($resp->body['msg'] === MSG_BAD_DECK,"Received MSG_BAD_DECK");

// Now let's create the challenge
$data = ['action' => 'create_challenge', '_fuser' => $u1->ID, 'deck_name' => "U{$u1->ID}DECK", 'opponent' => $u2->ID];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "create a challenge");

$game_id = $resp->body['game_id'];

echo "GameID=$game_id" . PHP_EOL;

// Now let's decline this game
$data = ['action' => 'decline_game', '_fuser' => $u2->ID, 'game' => $game_id];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "decline first game challenge");
test($resp->body['msg'] === MSG_GAME_DECLINED, "Received MSG_GAME_DECLINED");
$found = false;
foreach ( $resp->body['games']['others_games'] as $g ) {
    if ( $g->id == $game_id) {
        $found = true; 
    }    
}
test($found === false, "game is no longer in others_games");
$data = ['action' => 'decline_game', '_fuser' => $u2->ID, 'game' => $game_id];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "retry decline first game challenge");
test($resp->body['msg'] === MSG_GAME_NOT_FOUND, "Received MSG_GAME_NOT_FOUND");

// Now let's create another challenge
$data = ['action' => 'create_challenge', '_fuser' => $u1->ID, 'deck_name' => "U{$u1->ID}DECK", 'opponent' => $u2->ID];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "create a challenge");

$game_id = $resp->body['game_id'];

echo "GameID=$game_id" . PHP_EOL;

// This time u2 accepts the game

