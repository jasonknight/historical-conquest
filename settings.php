<?php
namespace HistoricalConquest;
function settings() {
    $o = new \stdClass;
    $o->starter_deck_count = 51;
    $o->booster_deck_count = 20;
    $o->minimum_deck_count = 50;
    $o->apply_to_scope_types = [
        'always_on',
        'anytime',
        'turn',
        'attack',
        'defense' 
    ];
    $o->apply_to_types = [
        'player',
        'opponent',
        'all_opponents', 
        'card_in_same_column',
        'card_played',
        'cards_in_column',
        'cards_played', 
        'opponent_card_in_same_column',
        'opponent_card_played',
        'opponent_cards_in_column',
        'opponent_cards_played',
    ];
    $o->card_types = [
        'land',
        'character' => [
            'activist',
            'artist',
            'assassin',
            'athlete',
            'author',
            'businessman',
            'conqueror',
            'entertainer',
            'economist',
            'explorer' => [
                'sea',
                'air',
                'land',
                'space' 
            ],
            'inventor',
            'law_enforcement',
            'leader',
            'musician',
            'outlaw' => [
                'pirate', 
                'mobster',
                'rebel',
            ],
            'philosopher',
            'politician',
            'scientist',
            'spirtual_leader',
            'warrior' 
        ], 
        'army',
        'vessel',
        'event',
        'knowledge',
        'organization',
        'technology',
        'location',
        'document'
    ];
    $o->usage_types = [
        'immediate',
        'hold', 
        'constant',
    ];
    $o->ability_types = [
        'interrupt',
        'choice',
        'multi', 
    ];
    return $o;
}
