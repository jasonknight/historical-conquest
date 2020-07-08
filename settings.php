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
        'gender_in_same_column',
        'religion_in_same_column',
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
            'assassin' => [
                'spy'    
            ],
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
                'land_sea',
                'space' 
            ],
            'inventor',
            'law_enforcement',
            'leader',
            'musician',
            'mathematician',
            'outlaw' => [
                'pirate', 
                'mobster',
                'rebel',
            ],
            'philosopher',
            'politician',
            'scientist',
            'spiritual_leader',
            'spy',
            'warrior' 
        ], 
        'army',
        'vessel',
        'aircraft',
        'event',
        'knowledge',
        'organization',
        'technology',
        'location',
        'document',
        'relic',
        'mathematicians',
    ];
	$o->card_religions = [
		'christian',
		'catholic',
		'orthodox_christian',
		'protestant',
		'muslim',
        'muslim_shiite',
        'muslim_sunni',
		'buddhist',
		'hindu',
		'atheist',
		'jewish',
    ];
    $o->card_continents = [
	    'north_america',
        'asia',
        'europe',
        'africa',
        'south_america',
        'antarctica',
        'oceania'   
	];
    $o->card_climates = [
        'tropical',
        'dry',
        'temperate',
        'continental',
        'polar', 
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
    $o->ethnicities = [
        'white',
        'black',
        'arab',
        'hispanic',
        'asian',
        'idigenous',
        'jewish', 
    ];
    return $o;
}
