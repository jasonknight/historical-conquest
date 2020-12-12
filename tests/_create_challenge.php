<?php
namespace HistoricalConquest;
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

echo "-------------------------------------------------------------------------" . PHP_EOL;
echo "-                         GAME CREATED = $game_id " . PHP_EOL;
echo "-------------------------------------------------------------------------" . PHP_EOL;

