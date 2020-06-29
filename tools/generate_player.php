<?php
namespace HistoricalConquest;
function get_random_explorer($cards) {
    $exps = [];
    foreach ($cards as $card ) {
        if ( $card->subtype1 == 'explorer') {
            $exps[] = $card->id;
        }
    }
    return $exps[rand(0,count($exps) - 1)];
}
function get_random_army($cards) {
    $exps = [];
    foreach ($cards as $card ) {
        if ( $card->type == 'army') {
            $exps[] = $card->id;
        }
    }
    return $exps[rand(0,count($exps) - 1)];
}
$lines = file(__DIR__ . '/master_list.tsv');
$hline = array_shift($lines);
$hline = explode("\t",trim($hline));
$cards = array_map(function ($l) use ($hline) {
    $parts = explode("\t",trim($l)); 
    $card = new \stdClass;
    for ( $i = 0; $i < count($hline); $i++ ) {
       $key = strtolower($hline[$i]);
       $card->{$key} = $parts[$i]; 
    } 
    return $card;
},$lines);
$final_cards = [];
foreach ( $cards as $card ) {
    $final_cards[ $card->id ] = $card;
}

$players = [];
for ( $i = 1; $i < 3; $i++ ) {
    $player = new \stdClass;
    $player->name = "Player" . $i;
    $player->id = $i;
    $player->hand = [
        get_random_explorer($final_cards), 
        get_random_army($final_cards), 
    ];
    $player->discard_pile = [];
    $player->land_pile = [];
    $player->draw_pile = [];
    $possible_cards = array_keys($final_cards);
    $land_count = 0;
    while ( count($player->draw_pile) < 50 ) {
        $id = $possible_cards[rand(0,count($possible_cards) - 1)];
        if ( $final_cards[$id]->type != 'land' && $land_count < 7 ) {
            if ( rand(0,100) < 60 ) 
               continue; 
        }
        if ( $final_cards[$id]->type == 'land' && $land_count > 12)
            continue;
        if ( $final_cards[$id]->type == 'land')
            $land_count++;
        $player->draw_pile[] = (string)$id;

    }
    $player->land_count = $land_count;
    $player->played = [];
    $player->playmat = [];
    array_push($players,$player);
}
$board = new \stdClass;
$board->round = 0;
$board->current_player = 1;
$board->players = $players;
return json_encode($board,JSON_PRETTY_PRINT);
