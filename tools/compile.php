<?php
function get_html_entries() {
    $html = file_get_contents(__DIR__ . '/card_master_list.html');
    $dom = new DomDocument();
    $dom->loadHTML($html);
    $dom->preserveWhiteSpace = false;
    $tables = $dom->getElementsByTagName('table');
    $rows = $tables->item(0)->getElementsByTagName('tr');
    $row1 = false;
    $keys = [];
    $entries = [];
    $stats = [];
    foreach ( $rows as $row ) {
        $cols = $row->getElementsByTagName('td');
        if ( $row1 === false ) {
            foreach ( $cols as $col ) {
                $keys[] = $col->nodeValue;
            } 
            $row1 = true;
        } else {
            $i = 0;
            $entry = [];
            foreach ( $cols as $col ) {
                $entry[ $keys[$i] ] = $col->nodeValue;
                $i++;
            }
            
            $entry['Type'] = str_replace(" ",'',$entry['Type']);
            $entry['Type'] = str_replace(" ",'',$entry['Type']);
            $entry['Type'] = str_replace('/','_',$entry['Type']);
            $entry['Type'] = str_replace('-','_',$entry['Type']);
            if ( ! isset($stats['by_type'][ $entry['Type'] ]) ) {
                $stats['by_type'][ $entry['Type'] ] = 0;
            }
            $stats['by_type'][ $entry['Type'] ]++;
            $entries[] = $entry;
        }
    }
    $card_types = [
            'land' => true,
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
                    'space',
                    'flight', 
                ],
                'inventor',
                'law_enforcement',
                'leader',
                'mathematician',
                'musician',
                'outlaw' => [
                    'mobster',
                    'rebel',
                    'pirate', 
                ],
                'philosopher',
                'politician',
                'scientist',
                'spirtual_leader',
                'spy',
                'warrior' 
            ], 
            'aircraft' => true,
            'army' => true,
            'vessel' => true,
            'event' => true,
            'knowledge' => true,
            'organization' => true,
            'technology' => true,
            'location' => true,
            'document' => true,
            'relic' => true,
        ];
    $id_base = 10000;
    $entries = array_map(function ($e) use($card_types,$id_base) {
        if ( empty( trim($e['ID']) ) ) {
            $e['ID'] = "D{$e['Deck']}I" . $id_base++;
        }
        foreach ( ['morale','strength','defense'] as $d ) {
            $e["{$d}_add"] = '';
            $e["{$d}_lose"] = '';
        }
        $e['ability_type'] = '';
        $e['usage_type'] = '';
        if ( preg_match("/gain ([\d]+) morale/",strtolower($e['Abilities']),$m) ) {
            $e['morale_add'] = $m[1];
        }
        if ( preg_match("/gain ([\d]+) strength/",strtolower($e['Abilities']),$m) ) {
            $e['strength_add'] = $m[1];
        }
        if ( preg_match("/gain ([\d]+) defense/",strtolower($e['Abilities']),$m) ) {
            $e['defense_add'] = $m[1];
        }
        // lose
        if ( preg_match("/lose ([\d]+) morale/",strtolower($e['Abilities']),$m) ) {
            $e['morale_lose'] = $m[1];
        }
        if ( preg_match("/lose ([\d]+) strength/",strtolower($e['Abilities']),$m) ) {
            $e['strength_lose'] = $m[1];
        }
        if ( preg_match("/lose ([\d]+) defense/",strtolower($e['Abilities']),$m) ) {
            $e['defense_lose'] = $m[1];
        }
        if ( preg_match("/INTERRUPT/", $e['Abilities']) ) {
            $e['ability_type'] = 'INTERRUPT';
        }
        if ( preg_match("/^CHOOSE/", $e['Abilities']) ) {
            $e['ability_type'] = 'CHOICE';
        }
        if ( preg_match("/^CHOICE/", $e['Abilities']) ) {
            $e['ability_type'] = 'CHOICE';
        }
        if ( preg_match("/CONSTANT/", $e['Abilities']) ) {
            $e['usage_type'] = 'CONSTANT';
        }
        
        if ( $e['Type'] == 'Lands' ) 
            $e['Type'] = 'Land';
        if ( $e['Type'] == 'Events' )
            $e['Type'] = 'Event';
        if ( $e['Type'] == 'Musicians' )
            $e['Type'] = 'Musician';
        if ( $e['Type'] == 'Activists' )
            $e['Type'] = 'Activist';
        if ( $e['Type'] == 'Documents' )
            $e['Type'] = 'Document';
        if ( $e['Type'] == 'Economists' )
            $e['Type'] = 'Economist';
        if ( $e['Type'] == 'Philosophers' )
            $e['Type'] = 'Philosopher';
        if ( $e['Type'] == 'Organizations' )
            $e['Type'] = 'Organization';
        if ( $e['Type'] == 'Warriors' )
            $e['Type'] = 'Warrior';
        if ( $e['Type'] == 'Aircrafts' )
            $e['Type'] = 'Aircraft';
        if ( $e['Type'] == 'Knowledge_Theory' )
            $e['Type'] = 'Knowledge';
        $e['SubType1'] = '';
        $e['SubType2'] = '';
        if ( isset($card_types[ strtolower($e['Type']) ] ) ) {
            $e['Type'] = strtolower($e['Type']);
        }
        if ( in_array(strtolower($e['Type']),$card_types['character']) ) {
            $t = strtolower($e['Type']);
            $e['Type'] = 'character';
            $e['SubType1'] = $t;
        }
        if ( preg_match('/Explorer_([\w]+)/',$e['Type'],$m) ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'explorer';
            $e['SubType2'] = strtolower($m[1]);
        }
        if ( preg_match('/Outlaw_([\w]+)/',$e['Type'],$m) ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'outlaw';
            $e['SubType2'] = strtolower($m[1]);
        }
        if ( $e['Type'] == 'SpiritualLeader' ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'spiritual_leader';
        }
        if ( $e['Type'] == 'SpiritualLeader_Politician' ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'spiritual_leader';
        }
        if ( $e['Type'] == 'LawEnforcers' ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'law_enforcement';
        }
        if ( $e['Type'] == 'Assassin_Spy' ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'assassin';
            $e['SubType2'] = 'spy';
        }
        if ( $e['Type'] == 'Outlaw' || $e['Type'] == 'Outlaws' ) {
            $e['Type'] = 'character';
            $e['SubType1'] = 'outlaw';
        }
        $keys = ['ID','Deck','Name','Year','Type','SubType1','SubType2','Attack Strength','Defense Strength','Summaries'];
        foreach ( ['morale','strength','defense'] as $d ) {
            $keys[] = "{$d}_add";
            $keys[] = "{$d}_lose";
        }
        $keys[] = 'ability_type';
        $keys[] = 'usage_type';
        $keys[] = 'Frequency';
        $keys[] = 'When to Play';
        $keys[] = 'Abilities';
        $new_e = [];
        foreach ( $keys as $key ) {
            $nkey = str_replace(' ','_',strtolower($key));
            $new_e[$nkey] = $e[$key];
        }
        return $new_e;
    },$entries);
    return $entries;
}
function get_summaries($entries) {
    $new_entries = [];
    foreach ($entries as $entry) {
        $new_entries[ $entry['id'] ] = $entry['summaries'];
    }
    return $new_entries;
}
$entries  = get_summaries(get_html_entries()); 
$lines = file(__DIR__ . '/master_list.tsv');
$hline = array_shift($lines);
$hline = explode("\t",trim($hline));
$cards = array_map(function ($l) use ($hline,$entries) {
    $parts = explode("\t",trim($l)); 
    $card = new stdClass;
    for ( $i = 0; $i < count($hline); $i++ ) {
       $key = strtolower($hline[$i]);
       $card->{$key} = $parts[$i]; 
    } 
    $card->summary = $entries[$card->id];
    return $card;
},$lines);
$final_cards = [];
foreach ( $cards as $card ) {
    $final_cards[ $card->id ] = $card;
}
ob_start();
echo json_encode($final_cards,JSON_PRETTY_PRINT);
$contents = ob_get_contents();
ob_end_clean();
file_put_contents(dirname(__DIR__) . "/assets/card-db.json",$contents);
