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
$board_a = file(__DIR__ . '/boards/AttackBoard_1_player_a.csv');
$board_a = array_map(function ($l) { return array_map('trim',explode("\t",$l)); },$board_a);
$board_b = file(__DIR__ . '/boards/AttackBoard_1_player_b.csv');
$board_b = array_map(function ($l) { return array_map('trim',explode("\t",$l)); },$board_b);
function get_land_pile_from_board($b) {
    $col = 8;
    $cards = [];
    for ( $row = 1; $row < 20; $row++ ) {
        if (!empty($b[$row][$col]) ) {
            $cards[] = $b[$row][$col];
        }
    }
    return $cards;
}
function get_draw_pile_from_board($b) {
    $col = 9;
    $cards = [];
    for ( $row = 1; $row < 20; $row++ ) {
        if ( !empty($b[$row][$col]) ) {
            $cards[] = $b[$row][$col];
        }
    }
    return $cards;
}
function get_hand_from_board($b) {
    $row = 8;
    $cards = [];
    for ( $col = 0; $col < 8; $col++ ) {
        if ( !empty($b[$row][$col]) ) {
            $cards[] = $b[$row][$col];
        }
    }
    return $cards;
}
function populate_grid_from_board($b,$g) {
    for ( $row = 1; $row < 9; $row++ ) {
        for ( $col = 0; $col < 8; $col++ ) {
             if ( !empty($b[$row][$col]) ) {
                $g[$row-1][$col] = $b[$row][$col];
             }   
        }
    }
    return $g;
}
$boards = [$board_a,$board_b];
for ( $i = 1; $i < 3; $i++ ) {
    $player = new \stdClass;
    $player->name = "Player" . $i;
    $player->id = $i;
    $player->morale = 0;
    $player->transport = [];
    $b = $boards[$i-1];
    $player->hand = get_hand_from_board($b);
    $player->discard_pile = [];
    $player->land_pile = get_land_pile_from_board($b);
    $player->draw_pile = get_draw_pile_from_board($b);; 
    $player->land_count = count($player->land_pile);
    $player->played = [];
    $player->playmat = get_base_table();
    $player->playmat = populate_grid_from_board($b,$player->playmat); 
    $player->abilitymat = get_base_table();
    $player->damagemat = get_base_table();
    array_push($players,$player);
}
$board = new \stdClass;
$board->round = 0;
$board->current_player = 1;
$board->players = $players;
return json_encode($board,JSON_PRETTY_PRINT);
