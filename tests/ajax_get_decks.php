<?php
namespace HistoricalConquest;
$data = ['action' => 'get_decks', '_fuser' => $u1->ID];
$resp = ajax_post($data);
test(is_array($resp->body),"get_decks should return an array");
test(array_key_exists('status',$resp->body),'get_decks should have a status key');
test($resp->body['status'] == 'OK','get_decks status should == OK');
test(array_key_exists('decks',$resp->body),'get_cards should have a decks key');

// Now let's create a deck
$dname = 'AjaxDeck' . rand(100,200);
$data = [
    'action' => 'create_deck',
    '_fuser' => $u1->ID,
    'deck_name' => $dname, 
];
$resp = ajax_post($data);
test($resp->body['status'] == 'OK',"deck $dname creation");
$found = false;
$found_id = 0;
foreach ( $resp->body['decks'] as $deck ) {
    if ( $deck['name'] == $dname ) {
        $found = true;
        $found_id = $deck['id'];
    }
}
test($found,"create_deck $dname with id of $found_id was found");
// Now we need to add some cards to the deck
// let's see if u1 has any cards
global $wpdb;
$card_ids_to_grant = [52,53,54];
$cards = $wpdb->get_results("SELECT * FROM `hc_player_cards` WHERE player_id = {$u1->ID}");
if ( empty($cards) ) {
    foreach ($card_ids_to_grant as $id ) {
        $wpdb->query("INSERT INTO `hc_player_cards` (player_id,card_id,created_at) VALUES ({$u1->ID},$id,NOW())");
    }
}
$data = ['action' => 'get_player_cards', '_fuser' => $u1->ID];
$resp = ajax_post($data);
$cards = $resp->body['cards'];
test(!empty($cards), "player must have some cards assigned");
$cards_to_add = array_filter($cards,function ($c) {
    global $card_ids_to_grant;
    return in_array($c['id'],$card_ids_to_grant);
});
$cards_to_add = array_map(function ($c) { return $c['ext_id']; },$cards_to_add);
// Now we can assign these cards
$data = ['action' => 'save_deck', '_fuser' => $u1->ID, 'deck_id' => $found_id, 'card_ids' => $cards_to_add];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK',"$dname was saved and cards were added");
$data = ['action' => 'get_deck_cards', '_fuser' => $u1->ID, 'deck_id' => $found_id];
$resp = ajax_post($data);
test($resp->body['status'] === 'OK',"fetched deck cards for $dname");
$cards = array_map(function ($c) { return $c['ext_id']; },$resp->body['cards']);
foreach ( $cards_to_add as $c2a ) {
    test(in_array($c2a,$cards), "$c2a found in cards!");
}
$data = ['action' => 'delete_deck', '_fuser' => $u1->ID, 'deck_id' => $found_id];
$resp = ajax_post($data);
$found = false;
$found_id = 0;
foreach ( $resp->body['decks'] as $deck ) {
    if ( $deck['name'] == $dname ) {
        $found = true;
        $found_id = $deck['id'];
    }
}
test($found === false,"delete_deck deleting $dname");
