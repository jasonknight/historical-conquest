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
$getP1AndP2 = function ($game_id) {
    global $u1,$u2;
    $errors = [];
    $p1 = null;
    $p2 = null;
    $players = get_players($game_id,[],$errors);
    foreach ( $players as $player ) {
        if ( $player->user_id == $u1->ID ) {
            $p1 = $player;
        } else {
            $p2 = $player;
        }
    }
    return [$p1,$p2];
};
function _show_board_debug($p) {
    echo "Player 1({$p->id})\n-------------------------------------------------------" . PHP_EOL;
    echo ascii_playmat($p->playmat) . PHP_EOL;
    echo "Ability Mat\n-------------------------------------------------------" . PHP_EOL;
    echo ascii_abilitymat($p->abilitymat) . PHP_EOL;
    echo ability_table($p) . PHP_EOL;
    echo card_table($p) . PHP_EOL;
}
function _get_board_for($uid,$game_id) {
    $data = ['action' => 'get_board', '_fuser' => $uid, 'game_id' => $game_id];
    $resp = ajax_post($data);
    test($resp->body['status'] === 'OK', "get game board");
    return $resp->body;
}
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
function _get_hand_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",$l); },$lines);
    // Hand starts at row 9
    $row = 8;
    $cards = [];
    for ( $col = 3; $col < 8; $col++ ) {
        $ext_id = $lines[$row][$col];
        $c = new \stdClass;
        $c->ext_id = $ext_id;
        $c->row = $row - 1;
        $c->col = $col;
        $cards[] = $c;
    }
    return $cards;
}
function _get_played_lands_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",$l); },$lines);
    $row = 7;
    $cards = [];
    for ( $col = 0; $col < 6; $col++ ) {
        $ext_id = $lines[$row][$col];
        if ( empty(trim($ext_id)) ) {
            continue;
        }
        $c = new \stdClass;
        $c->ext_id = $ext_id;
        $c->row = $row - 1;
        $c->col = $col;
        $cards[] = $c;
    }
    return $cards;
}
function _get_played_active_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",$l); },$lines);
    // Hand starts at row 9
    $cards = [];
    for ( $row = 0; $row < 7; $row++ ) {
        for ( $col = 6; $col < 8; $col++ ) {
            $ext_id = $lines[$row][$col];
            if ( empty(trim($ext_id) ) )
                continue;
            $c = new \stdClass;
            $c->ext_id = $ext_id;
            $c->row = $row - 1;
            $c->col = $col;
            $cards[] = $c;
        }
    }
    return $cards;
}
function _get_played_cards_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",$l); },$lines);
    // Hand starts at row 9
    $cards = [];
    for ( $row = 0; $row < 7; $row++ ) {
        for ( $col = 0; $col < 6; $col++ ) {
            $ext_id = $lines[$row][$col];
            if ( empty(trim($ext_id) ) )
                continue;
            $c = new \stdClass;
            $c->ext_id = $ext_id;
            $c->row = $row - 1;
            $c->col = $col;
            $cards[] = $c;
        }
    }
    return $cards;
}
function _get_land_pile_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",$l); },$lines);
    // Hand starts at row 9
    $cards = [];
    for ( $row = 1; $row < 15; $row++ ) {
        $col = 8;
        $ext_id = $lines[$row][$col];
        if ( empty(trim($ext_id) ) )
            continue;
        $c = new \stdClass;
        $c->ext_id = $ext_id;
        $c->row = $row;
        $c->col = $col;
        $cards[] = $c;
    }
    return $cards;
}
function _get_draw_pile_from_board($name) {
    $fname = dirname(__DIR__) . '/tools/boards/' . $name;
    test(file_exists($fname),"Checking to see if $fname exists");
    $lines = file($fname);
    $lines = array_map(function ($l) { return explode("\t",$l); },$lines);
    // Hand starts at row 9
    $cards = [];
    for ( $row = 1; $row < 16; $row++ ) {
        $col = 9;
        $ext_id = $lines[$row][$col];
        if ( empty(trim($ext_id) ) )
            continue;
        $c = new \stdClass;
        $c->ext_id = $ext_id;
        $c->row = $row;
        $c->col = $col;
        $cards[] = $c;
    }
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
include (__DIR__ . '/_create_challenge.php');
//print_r(get_logs_for(['system_play_card']));
// Let's test some stuff, I should not be able to draw a card

$data = ['action' => 'draw_card', '_fuser' => $u1->ID, 'game_id' => $game_id,'player_id' => $p1->id];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "should not be able to draw a card");
$data = ['action' => 'draw_card', '_fuser' => $u2->ID, 'game_id' => $game_id,'player_id' => $p1->id];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "should not be able to draw a card for another player");

// Now we will discard from the hand
$to_discard_from_hand = array_pop($p1->hand);
$data = [
    'action' => 'discard', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'player_id' => $p1->id,
    'ext_id' => $to_discard_from_hand,
    'hint' => 'discard_from_hand',
];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "We should be able to discard from our hand");
print_r(get_logs_for(['discard','save_player_mats','get_game_board','get_players'],$resp->body['logs']));
$data = ['action' => 'draw_card', '_fuser' => $u1->ID, 'game_id' => $game_id,'player_id' => $p1->id];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "We should be able to draw now");

$data = [
    'action' => 'discard', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'player_id' => $p1->id,
    'ext_id' => 'IN4302',
    'row' => 2,
    'col' => 5,
    'hint' => 'discard_from_playmat',
];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "We should be able to discard from our playmat");

list($p1,$p2) = $getP1AndP2($game_id);
test((string)$p1->playmat[2][4] === '0',"IN4302 should be discarded");
test(!in_array($to_discard_from_hand,$p1->hand), "$to_discard_from_hand should not be in the hand");
test(in_array($to_discard_from_hand,$p1->discard_pile), "$to_discard_from_hand should be in our discard_pile");
test(in_array('IN4302',$p1->discard_pile), "$to_discard_from_hand should be in our discard_pile");

// So now we need to test an attack, p1 will attack p2 from a land card
// p1 will use CT4303 The Philipines to Attack CT4203 Japan on p2's board
// p1 should lose this battle
echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "- ATTACK 1 $game_id" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
$old_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'AC4302'; });
test(!empty($old_p1cards), "Assert that AC4302 is present before the attack");
$data = [
    'action' => 'attack_player', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'attacker' => $p1->id,
    'defender' => $p2->id,
    'src_ext_id' => 'CT4303',
    'attacker_land_ext_id' => 'CT4303',
    'defender_land_ext_id' => 'CT4203',
];
$resp = ajax_post($data);
print_r($resp->body['errors']);
print_r(get_logs_for(['attack_player','get_attack_'],$resp->body['logs']));
print_r($resp->body['battle_report']);
test(intval($resp->body['battle_report']['winner']) === intval($p2->id),"assume p2 won this round");
test(intval($resp->body['battle_report']['loser']) === intval($p1->id),"assume p1 lost this round");
test($resp->body['battle_report']['lost_card'] === 'AC4302',"Assert p1 lost AC4302, Isabella Baumfree");
list($p1,$p2) = $getP1AndP2($game_id);
$new_p1_morale = get_player_morale($p1);
//print_r(get_logs_for(['get_player_morale']));
test(intval($p1_morale) > intval($new_p1_morale), "Assert p1 has less morale");
test((intval($p1_morale) - intval($new_p1_morale)) === 100, "Assert p1 has less morale by 100");
$new_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'AC4302'; });
test(empty($new_p1cards),"Assert that AC4302 is gone from the players playmat");
echo "Player 1({$p1->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p1->playmat) . PHP_EOL;
echo card_table($p1) . PHP_EOL;
echo "------------------------------------------\n";

echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "- ATTACK 2 $game_id" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
$old_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'MU4301'; });
test(!empty($old_p1cards), "Assert that MU4301 is present before the attack");
$data = [
    'action' => 'attack_player', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'attacker' => $p1->id,
    'defender' => $p2->id,
    'src_ext_id' => 'CT4303',
    'attacker_land_ext_id' => 'CT4303',
    'defender_land_ext_id' => 'CT4203',
];
$resp = ajax_post($data);
print_r(get_logs_for(['attack_player','get_attack_'],$resp->body['logs']));
print_r($resp->body['battle_report']);
print_r($resp->body['errors']);
test(intval($resp->body['battle_report']['winner']) === intval($p2->id),"assume p2 won this round");
test(intval($resp->body['battle_report']['loser']) === intval($p1->id),"assume p1 lost this round");
test($resp->body['battle_report']['lost_card'] === 'MU4301',"Assert p1 lost MU4301, Isabella Baumfree");
list($p1,$p2) = $getP1AndP2($game_id);
$new_p1_morale = get_player_morale($p1);
//print_r(get_logs_for(['get_player_morale']));
test(intval($p1_morale) > intval($new_p1_morale), "Assert p1 has less morale");
test((intval($p1_morale) - intval($new_p1_morale)) === 400, "Assert p1 has less morale by 400 $p1_morale - $new_p1_morale");
$new_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'MU4301'; });
test(empty($new_p1cards),"Assert that MU4301 is gone from the players playmat");
echo "Player 1({$p1->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p1->playmat) . PHP_EOL;
echo card_table($p1) . PHP_EOL;
echo "------------------------------------------\n";

echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "- ATTACK 3 $game_id" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
$old_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'EX4303'; });
test(!empty($old_p1cards), "Assert that EX4303 is present before the attack");
$data = [
    'action' => 'attack_player', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'attacker' => $p1->id,
    'defender' => $p2->id,
    'src_ext_id' => 'CT4303',
    'attacker_land_ext_id' => 'CT4303',
    'defender_land_ext_id' => 'CT4203',
];
$resp = ajax_post($data);
print_r(get_logs_for(['attack_player','get_attack_'],$resp->body['logs']));
test(intval($resp->body['battle_report']['winner']) === intval($p2->id),"assume p2 won this round");
test(intval($resp->body['battle_report']['loser']) === intval($p1->id),"assume p1 lost this round");
test($resp->body['battle_report']['lost_card'] === 'EX4303',"Assert p1 lost EX4303, Isabella Baumfree");
list($p1,$p2) = $getP1AndP2($game_id);
$new_p1_morale = get_player_morale($p1);
//print_r(get_logs_for(['get_player_morale']));
test(intval($p1_morale) > intval($new_p1_morale), "Assert p1 has less morale");
test((intval($p1_morale) - intval($new_p1_morale)) === 700, "Assert p1 has less morale by 700 $p1_morale - $new_p1_morale");
$new_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'EX4303'; });
test(empty($new_p1cards),"Assert that EX4303 is gone from the players playmat");
echo "Player 1({$p1->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p1->playmat) . PHP_EOL;
echo card_table($p1) . PHP_EOL;
echo "------------------------------------------\n";
// Okay, p1 has stupidly attacked and lost, and now they are going to cede the turn
$data = [
    'action' => 'cede_turn', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'player_id' => $p1->id,
];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "p1 cedes the turn to p2");

// ensure that p1 cannot cede the turn again
$data = [
    'action' => 'cede_turn', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'player_id' => $p1->id,
];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "p1 tries to cede the turn to p2, but it's not p1's turn");

$data = [
    'action' => 'get_current_player', 
    '_fuser' => $u2->ID, 
    'game_id' => $game_id,
    'player_id' => $p2->id,
];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "check that p2 is now the current player");
test(intval($resp->body['player']['id']) === intval($p2->id),"check that p2 is the current player by id");

echo "Now p2 Japan will attack the Philipines" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "- ATTACK 4 $game_id" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
$old_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'AR4303'; });
test(!empty($old_p1cards), "Assert that AR4303 is present before the attack");
$data = [
    'action' => 'attack_player', 
    '_fuser' => $u2->ID, 
    'game_id' => $game_id,
    'attacker' => $p2->id,
    'defender' => $p1->id,
    'src_ext_id' => 'CT4203',
    'defender_land_ext_id' => 'CT4303',
    'attacker_land_ext_id' => 'CT4203',
];
$resp = ajax_post($data);
print_r(get_logs_for(['attack_player','get_attack_','system_play'],$resp->body['logs']));
test(intval($resp->body['battle_report']['winner']) === intval($p2->id),"assume p2 won this round");
test(intval($resp->body['battle_report']['loser']) === intval($p1->id),"assume p1 lost this round");
test($resp->body['battle_report']['lost_card'] === 'AR4303',"Assert p1 lost AR4303, " . get_card_name('AR4303'));
print_r($resp->body['battle_report']);
list($p1,$p2) = $getP1AndP2($game_id);
$new_p1_morale = get_player_morale($p1);
echo "New Morale: $new_p1_morale" . PHP_EOL;
test(intval($p1_morale) > intval($new_p1_morale), "Assert p1 has less morale");
test((intval($p1_morale) - intval($new_p1_morale)) === 800, "Assert p1 has less morale by 800 $p1_morale - $new_p1_morale");
$new_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'AR4303'; });
test(empty($new_p1cards),"Assert that AR4303 is gone from the players playmat");
echo "Player 1({$p1->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p1->playmat) . PHP_EOL;
echo card_table($p1) . PHP_EOL;
echo "------------------------------------------\n";

echo "The Philipines is now unguarded, so attacking should add that to our played lands" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "- ATTACK 5 $game_id" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "Player 2({$p2->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p2->playmat) . PHP_EOL;
echo ability_table($p1) . PHP_EOL;
echo card_table($p2) . PHP_EOL;
echo "------------------------------------------\n";
$old_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'CT4303'; });
test(!empty($old_p1cards), "Assert that CT4303 is present before the attack");
$data = [
    'action' => 'attack_player', 
    '_fuser' => $u2->ID, 
    'game_id' => $game_id,
    'attacker' => $p2->id,
    'defender' => $p1->id,
    'src_ext_id' => 'CT4203',
    'defender_land_ext_id' => 'CT4303',
    'attacker_land_ext_id' => 'CT4203',
];
$resp = ajax_post($data);
print_r($resp->body['battle_report']);
print_r($resp->body['messages']);
print_r(get_logs_for(['attack_player','get_attack_','system_play'],$resp->body['logs']));
test(intval($resp->body['battle_report']['winner']) === intval($p2->id),"assume p2 won this round");
test(intval($resp->body['battle_report']['loser']) === intval($p1->id),"assume p1 lost this round");
test($resp->body['battle_report']['lost_card'] === 'CT4303',"Assert p1 lost CT4303, " . get_card_name('CT4303'));
test($resp->body['battle_report']['transferred_land'] === 'CT4303',"Assert p2 received CT4303, " . get_card_name('CT4303'));
print_r($resp->body['battle_report']);
list($p1,$p2) = $getP1AndP2($game_id);
$new_p1_morale = get_player_morale($p1);
echo "New Morale: $new_p1_morale" . PHP_EOL;
test(intval($p1_morale) > intval($new_p1_morale), "Assert p1 has less morale");
$new_p1cards = array_filter(get_played_cards_from_player($p1),function ($c) { return $c->ext_id == 'CT4303'; });
test(empty($new_p1cards),"Assert that AR4303 is gone from the players playmat");
_show_board_debug($p1);
_show_board_debug($p2);
list($p1,$p2) = $getP1AndP2($game_id);
$p1board = _get_board_for($u1,$game_id);
print_r(get_logs_for(['get_game_board'],$resp->body['logs']));
test(intval($p1board['winner_id']) === intval($p2->id), "Assert that p2 has won the game!");

$data = [
    'action' => 'play_card', 
    '_fuser' => $u2->ID, 
    'game_id' => $game_id,
    'player_id' => $p2->id,
    'card_ext_id' => array_pop($p2->hand),
    'row' => 5,
    'col' => 1,
];
$resp = ajax_post($data);
test($resp->body['status'] == 'KO', "We should not be abled to play_card after winning");
test(in_array(MSG_GAME_OVER,$resp->body['errors']), "MSG_GAME_OVER is in the errors array");
$data = [
    'action' => 'cede_turn', 
    '_fuser' => $u2->ID, 
    'game_id' => $game_id,
    'player_id' => $p2->id,
];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "p2 should not be able to cede turn");

echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "-                 Testing Explorer Functionality               " . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;

include (__DIR__ . '/_create_challenge.php');

list($p1,$p2) = $getP1AndP2($game_id);

_show_board_debug($p1);
// First let's put an explorer into p1's hand
array_pop($p1->hand);
$p1->hand = array_values(array_filter($p1->hand,function ($id) { return $id !== 'EX4205'; }));
$p1->hand[] = 'EX4205';
$errors = [];
save_player_mats($p1,$errors);
print_r(get_logs_for(['save_player'],action_log('')));
test(empty($errors), ' saving p1s mat should gen no errors');
list($p1,$p2) = $getP1AndP2($game_id);
test( !empty(array_filter($p1->hand,function ($id) { return $id === 'EX4205'; })), "Mansa should be in the hand");
_show_board_debug($p1);
$data = [
    'action' => 'play_card', 
    '_fuser' => $u1->ID, 
    'game_id' => $game_id,
    'player_id' => $p1->id,
    'card_ext_id' => 'EX4205',
    'row' => 5,
    'col' => 2,
];

$resp = ajax_post($data);
test($resp->body['status'] == 'OK', "Assert we were able to play Mansa");
list($p1,$p2) = $getP1AndP2($game_id);
test($p1->playmat[5][2] === 'EX4205', "Mansa has been played");
test(type_to_name(get_card_def($p1->playmat[5][1])->maintype) === 'CARD_LAND', "a land has been played");

array_pop($p1->hand);
array_pop($p1->hand);
$p1->hand = array_values(array_filter($p1->hand,function ($id) { return $id !== 'WA4301'; }));
$p1->hand[] = 'WA4301';
$p1->hand = array_values(array_filter($p1->hand,function ($id) { return $id !== 'CO4303'; }));
$p1->hand[] = 'CO4303';
$errors = [];
save_player_mats($p1,$errors);
$i = 0;
foreach (['WA4301','CO4303'] as $ext_id ) {
    $data = [
        'action' => 'play_card', 
        '_fuser' => $u1->ID, 
        'game_id' => $game_id,
        'player_id' => $p1->id,
        'card_ext_id' => $ext_id,
        'row' => 4 - $i,
        'col' => 2,
    ];
    $i++;
    $resp = ajax_post($data);
    test($resp->body['status'] == 'OK', "Assert we played $ext_id");
}
list($p1,$p2) = $getP1AndP2($game_id);
_show_board_debug($p1);

exit;

echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "-        AUTOMATED TESTS COMPLETE, CREATING MANUAL TESTING GAME" . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;

include (__DIR__ . '/_create_challenge.php');
