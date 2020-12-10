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
function _get_board_for($uid) {
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
    // Hand starts at row 9
    $row = 7;
    $cards = [];
    for ( $col = 3; $col < 6; $col++ ) {
        $ext_id = $lines[$row][$col];
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
$data = ['action' => 'accept_game', '_fuser' => $u2->ID, 'deck_name' => "U{$u2->ID}DECK",'game' => $game_id];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "u2 accepts the challenge");

// Now we need to modify the game so that it is more predictable
// 1. We need to set u1 as the next player
// 2. We need to update the player table to have the same setup as the .csv board
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
list($p1,$p2) = $getP1AndP2($game_id);
// set p1 as the next player
$sql = "UPDATE `hc_games` SET current_player_id = {$p1->id} WHERE id = $game_id";
$wpdb->query($sql);
test(empty($wpdb->last_error),"update the current player");

$data = ['action' => 'get_board', '_fuser' => $u1->ID, 'game_id' => $game_id];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "get game board");
test(intval($resp->body['round']) === 1, "check that it's the first round");
test(intval($resp->body['current_player_id']) === intval($p1->id), "check that p1({$p1->id}) == {$resp->body['current_player_id']} is the current player");
// Now that we have this, we need to make some moves, to see if we can play cards
$p1board = $resp->body;
$data = ['action' => 'get_board', '_fuser' => $u2->ID, 'game_id' => $game_id];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK', "get game board for u2");
$p2board = $resp->body;

// Find out if the opposing players hands are hidden from one another
// first, check p1
foreach( $p1board['players'] as $bp ) {
    if ( intval($bp['id']) != intval($p1->id) ) {
        test(empty($bp['hand']),"assure that {$bp['id']} hand is hidden from {$p1->id}");
    } else {
        $h = join(',',$bp['hand']);
        $h2 = join(',',$p1->hand);
        test( $h == $h2, "assure $h == $h2 for {$p1->id}");
    }
}
foreach( $p2board['players'] as $bp ) {
    if ( intval($bp['id']) != intval($p2->id) ) {
        test(empty($bp['hand']),"assure that {$bp['id']} hand is hidden from {$p2->id}");
    } else {
        $h = join(',',$bp['hand']);
        $h2 = join(',',$p2->hand);
        test( $h == $h2, "assure $h == $h2 for {$p2->id}");
    }
}
// Now we need to set the board to look like something we know
// about, instead of empty or random
$getExtIds = function ($ar) {
    return array_map(function ($e) { return $e->ext_id; },$ar);
};

$sql = "UPDATE `hc_players` SET ";
$sets = [];

$hand = _get_hand_from_board('AttackBoard_1_player_a.csv');
$hand = array_map(function ($c) { return trim($c->ext_id); },$hand);
$sets[] = $wpdb->prepare("hand = %s",json_encode($hand));

$land_pile = _get_land_pile_from_board('AttackBoard_1_player_a.csv');
$land_pile = array_map(function ($c) { return trim($c->ext_id); },$land_pile);
$sets[] = $wpdb->prepare("landpile = %s",json_encode($land_pile));

$draw_pile = _get_draw_pile_from_board('AttackBoard_1_player_a.csv');
$draw_pile = array_map(function ($c) { return trim($c->ext_id); },$draw_pile);
$sets[] = $wpdb->prepare("drawpile = %s",json_encode($draw_pile));

$played_lands = _get_played_lands_from_board('AttackBoard_1_player_a.csv');
$played_active = _get_played_active_from_board('AttackBoard_1_player_a.csv');

$played_cards = array_merge(_get_played_cards_from_board('AttackBoard_1_player_a.csv'),$played_lands,$played_active);

$sql .= join(",",$sets);

// Now we update p1 and p2

$p1_sql = $sql . " WHERE id = {$p1->id}";

echo $p1_sql . PHP_EOL;
$wpdb->query($p1_sql);
test(empty($wpdb->last_error), "update p1 hand,draw,land " . $wpdb->last_error );

list($p1,$p2) = $getP1AndP2($game_id);
// Now we have to loop over the played cards and play them into the playmat of p1
$errors = [];
foreach ( $played_cards as $pcs ) {
    $ext_id = trim($pcs->ext_id);
    echo "game_id=$game_id,pid={$p1->id},ext_id=$ext_id,row={$pcs->row},col={$pcs->col}" . PHP_EOL;
    system_play_card($game_id,$p1,$ext_id,$pcs->row,$pcs->col,false,true,$errors);
} 
list($p1,$p2) = $getP1AndP2($game_id);

// Now do the whole thing again for p2
$sql = "UPDATE `hc_players` SET ";
$sets = [];
$hand = _get_hand_from_board('AttackBoard_1_player_b.csv');
$hand = array_map(function ($c) { return trim($c->ext_id); },$hand);
$sets[] = $wpdb->prepare("hand = %s",json_encode($hand));

$land_pile = _get_land_pile_from_board('AttackBoard_1_player_b.csv');
$land_pile = array_map(function ($c) { return trim($c->ext_id); },$land_pile);
$sets[] = $wpdb->prepare("landpile = %s",json_encode($land_pile));

$draw_pile = _get_draw_pile_from_board('AttackBoard_1_player_b.csv');
$draw_pile = array_map(function ($c) { return trim($c->ext_id); },$draw_pile);
$sets[] = $wpdb->prepare("drawpile = %s",json_encode($draw_pile));

$played_lands = _get_played_lands_from_board('AttackBoard_1_player_b.csv');
$played_active = _get_played_active_from_board('AttackBoard_1_player_b.csv');

$played_cards = array_merge(_get_played_cards_from_board('AttackBoard_1_player_b.csv'),$played_lands,$played_active);

$sql .= join(",",$sets);
$p2_sql = $sql . " WHERE id = {$p2->id}";

echo $p2_sql . PHP_EOL;
$wpdb->query($p2_sql);
test(empty($wpdb->last_error), "update p2 hand,draw,land " . $wpdb->last_error );
$errors = [];
foreach ( $played_cards as $pcs ) {
    $ext_id = trim($pcs->ext_id);
    echo "game_id=$game_id,pid={$p2->id},ext_id=$ext_id,row={$pcs->row},col={$pcs->col}" . PHP_EOL;
    system_play_card($game_id,$p2,$ext_id,$pcs->row,$pcs->col,false,true,$errors);
} 
list($p1,$p2) = $getP1AndP2($game_id);
echo "Player 1({$p1->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p1->playmat).PHP_EOL;
echo "------------------------------------------\n";
echo ascii_abilitymat($p1->abilitymat) . PHP_EOL;
echo card_table($p1) . PHP_EOL;
echo ability_table($p1) . PHP_EOL;
echo "Player 2({$p2->id})\n--------------------------------" . PHP_EOL;
echo ascii_playmat($p2->playmat).PHP_EOL;
echo "------------------------------------------\n";
echo ascii_abilitymat($p2->abilitymat).PHP_EOL;
echo card_table($p2) . PHP_EOL;
echo ability_table($p2) . PHP_EOL;
$p1_morale = get_player_morale($p1);
echo "P1 Morale: " . $p1_morale . PHP_EOL;
$p2_morale = get_player_morale($p2);
echo "P2 Morale: " . $p2_morale . PHP_EOL;
test(get_player_morale($p1) > 800,"test that p1 has 800+ morale");
test(get_player_morale($p2) > 800,"test that p2 has 800+ morale");
//print_r(get_logs_for(['system_play_card']));
// Let's test some stuff, I should not be able to draw a card

$data = ['action' => 'draw_card', '_fuser' => $u1->ID, 'game_id' => $game_id,'player_id' => $p1->id];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "should not be able to draw a card");
$data = ['action' => 'draw_card', '_fuser' => $u2->ID, 'game_id' => $game_id,'player_id' => $p1->id];
$resp = ajax_post($data);
test($resp->body['status'] === 'KO', "should not be able to draw a card for another player");

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
print_r(get_logs_for(['attack_player','get_attack_'],$resp->body['logs']));
test(intval($resp->body['battle_report']['winner']) === intval($p2->id),"assume p2 won this round");
test(intval($resp->body['battle_report']['loser']) === intval($p1->id),"assume p1 lost this round");
print_r($resp->body['battle_report']);
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
print_r(get_logs_for(['attack_player','get_attack_'],$resp->body['logs']));
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
