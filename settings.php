<?php
namespace HistoricalConquest;
function settings() {
    $o = new \stdClass;
    $o->starter_deck_count = 51;
    $o->booster_deck_count = 20;
    $o->minimum_deck_count = 50;
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
            'outlaw',
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
        'immediate' => 0,
        'hold' => 1, 
        'constant' => -1,
    ];
    $o->ability_types = [
        'interrupt',
        'choice',
        'multi', 
    ];
    return $o;
}
