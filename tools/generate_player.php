<?php
namespace HistoricalConquest;
function get_random_explorer($cards) {
    $exps = [];
    foreach ($cards as $card ) {
        $card = convert_to_std($card);
        if ( preg_match('/EXPLORER/',type_to_name($card->maintype)) ) {
            $exps[] = $card->ext_id;
        }
    }
    return $exps[rand(0,count($exps) - 1)];
}
function get_random_army($cards) {
    $exps = [];
    foreach ($cards as $card ) {
        $card = convert_to_std($card);
        if ( $card->maintype == CARD_ARMY) {
            $exps[] = $card->ext_id;
        }
    }
    return $exps[rand(0,count($exps) - 1)];
}
function get_random_character($cards) {
    $exps = [];
    foreach ($cards as $card ) {
        $card = convert_to_std($card);
        if ( preg_match('/CARD_CHARACTER/',type_to_name($card->maintype)) ) {
            $exps[] = $card->ext_id;
        }
    }
    return $exps[rand(0,count($exps) - 1)];

}
$cards = get_cards();
$final_cards = [];
foreach ( $cards as $card ) {
    $card = convert_to_std($card);
    $final_cards[ $card->ext_id ] = $card;
}
$players = [];
for ( $i = 1; $i < 3; $i++ ) {
    $player = new \stdClass;
    $player->name = "Player" . $i;
    $player->id = $i;
    $player->morale = 1001;
    $player->transport = [];
    $player->hand = [
        get_random_explorer(get_cards_with_abilities()), 
        get_random_army(get_cards_with_abilities()), 
        get_random_character(get_cards_with_abilities()), 
    ];
    $player->discard_pile = [];
    $player->land_pile = [];
    $player->draw_pile = [];
    $possible_cards = array_keys($final_cards);
    $land_count = 0;
    while ( count($player->draw_pile) < 50 ) {
        $id = $possible_cards[rand(0,count($possible_cards) - 1)];
        if ( $final_cards[$id]->maintype != CARD_LAND && $land_count < 7 ) {
            if ( rand(0,100) < 60 ) 
               continue; 
        }
        if ( $final_cards[$id]->maintype == CARD_LAND && $land_count > 12)
            continue;
        if ( $final_cards[$id]->maintype == CARD_LAND)
            $land_count++;
        $player->draw_pile[] = (string)$id;

    }
    $player->land_count = $land_count;
    $player->played = [];
    $player->playmat = get_base_table();
    $player->effectmat = get_base_table();
    $player->damagemat = get_base_table();
    array_push($players,$player);
}
$board = new \stdClass;
$board->round = 0;
$board->current_player = 1;
$board->players = $players;
return json_encode($board,JSON_PRETTY_PRINT);
