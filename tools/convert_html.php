<?php
require_once(dirname(__DIR__) . '/types.php');
require_once(dirname(__DIR__) . '/settings.php');

$lines = file(__DIR__ . '/continents.txt');
$lines = array_slice($lines,1);
$lands_to_continents = [];
$ptr = '';
foreach ( $lines as $line ) {
    $line = trim($line);
    if ( empty($line) )
        continue;
    $parts = explode(',',$line);
    $ptr = $parts[0];
    if ( ! isset($lands_to_continents[$ptr]) ) {
        $lands_to_continents[$ptr] = [];
    }
    $lands_to_continents[$ptr][] = $parts[1];
}
$lines = file(__DIR__ . '/states.txt');
$lines = array_map('trim',$lines);
$ptr = 'North America';
foreach ( $lines as $line ) {
    $lands_to_continents[$ptr][] = $line;
}
$html = file_get_contents(__DIR__ . '/masterlist.xhtml');
$dom = new DomDocument();
$dom->loadHTML($html);
$dom->preserveWhiteSpace = false;
$tables = $dom->getElementsByTagName('table');
$entries = [];
$stats = [];
$s = HistoricalConquest\settings();
$card_types = $s->card_types;
foreach ( $tables as $table ) {
    $rows = $table->getElementsByTagName('tr');
    $row1 = false;
    $keys = [];
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
                $spans = $col->getElementsByTagName('span');
                $value = $col->nodeValue;
                foreach ( $spans as $span ) {
                    $svalue = $span->nodeValue;
                    $value = str_replace($svalue,'',$value);
                }
                $entry[ $keys[$i] ] = $value;
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
            $entry['Name'] = preg_replace('/\xc2\xa0/', ' ', trim($entry['Name']));
            if ( strlen(trim($entry['Name'])) < 3 ) {
                continue;
            }
            if ( preg_match("/ANNOTATION/", $entry['name']) ) {
                continue;
            }
            $entries[] = $entry;
        }
    }
}
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
        if ( $e['SubType2'] == 'flight' ) {
            $e['SubType2'] = 'air';
        }
    }
    foreach ($card_types['character'] as $c ) {
        if ( strtolower($e['Type']) === $c ) {
            $e['Type'] = 'character';
            $e['Subtype1'] = $c;
        }
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
    if ( $e['Type'] == 'Activist' ) {
        $e['Type'] = 'character';
        $e['SubType1'] = 'activist';
    }
     
    if ( $e['Type'] == 'Authors' ) {
        $e['Type'] = 'character';
        $e['SubType1'] = 'author';
    }
    if ( $e['Type'] == 'Leaders' ) {
        $e['Type'] = 'character';
        $e['SubType1'] = 'leader';
    }
    if ( $e['Type'] == 'LawEnforcer' ) {
        $e['Type'] = 'character';
        $e['SubType1'] = 'law_enforcement';
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
    if ( $e['Type'] == 'Outlaws_Pirates' ) {
        $e['Type'] = 'character';
        $e['SubType1'] = 'outlaw';
        $e['SubType2'] = 'pirate';
    }
    if ( $e['Type'] == 'Outlaw_Pirates' ) {
        $e['Type'] = 'character';
        $e['SubType1'] = 'outlaw';
        $e['SubType2'] = 'pirate';
    }
    if ( $e['Type'] == 'Mathmaticians' ) {
        $e['Type'] = 'Mathematicians';
    }
    $keys = ['ID','Deck','Name','Year','Type','SubType1','SubType2','Attack Strength','Defense Strength'];
    foreach ( ['morale','strength','defense'] as $d ) {
        $keys[] = "{$d}_add";
        $keys[] = "{$d}_lose";
    }
    $keys[] = 'ability_type';
    $keys[] = 'usage_type';
    $keys[] = 'Frequency';
    $keys[] = 'When to Play';
    $keys[] = 'Abilities';
    $keys[] = 'Summaries';
    $keys[] = 'Reference';
    $keys[] = 'Reference 2';
    $new_e = [];
    foreach ( $keys as $key ) {
        $nkey = str_replace(' ','_',strtolower($key));
        $new_e[$nkey] = $e[$key];
    }
    return $new_e;
},$entries);
// Now let's fix the types
$insertable_entries = [];
foreach ( $entries as $e ) {
    $entry = [];
    $entry['name'] = $e['name']; 
    $type_path = array_filter([$e['type'],$e['subtype1'],$e['subtype2']],function ($r) { return !empty($r); });
    $entry['maintype'] = 'CARD_' . strtoupper(join('_',$type_path));
    if ( $entry['maintype'] == 'CARD_CHARACTER_OUTLAW_PIRATES' ) {
        $entry['maintype'] = 'CARD_CHARACTER_OUTLAW_PIRATE';
    }
    if ( $entry['maintype'] == 'CARD_ASSASSIN' ) {
        $entry['maintype'] = 'CARD_CHARACTER_ASSASSIN';
    }
    if ( ! defined($entry['maintype']) ) {
        echo $entry['maintype'] . " is not defined!" . PHP_EOL;
        print_r($entry);
        exit;
    }
    $entry['deck'] = $e['deck'];
    $entry['created_at'] = date("Y-m-d H:i:s");
    $entry['reference'] = $e['reference'];
    $entry['reference2'] = $e['reference_2'];
    $entry['ext_id'] = $e['id'];
    $entry['strength'] = $e['attack_strength'];
    $entry['defense'] = $e['attack_defense'];
    $entry['when_to_play'] = $e['when_to_play'];
    $entry['year'] = $e['year'];
    $entry['gender'] = 'male';
    $entry['summary'] = $e['summaries'];
    $entry['abilities'] = $e['abilities'];
    $entry['continent'] = 0;
    $insertable_entries[] = $entry;
}
foreach ( $insertable_entries as &$i ) {
    if ( preg_match("/ she /",strtolower($i['summary'])) ) {
        $i['gender'] = 'female';
    }
    if ( preg_match("/ woman /",strtolower($i['summary'])) ) {
        $i['gender'] = 'female';
    }
    
    if ( $i['maintype'] == 'CARD_LAND' ) {
        foreach ( $lands_to_continents as $continent=>$lands ) {
            foreach ( $lands as $land ) {
                if ( $continent == 'Asia' && $i['name'] == 'Russia' ) {
//                    echo "$continent -> $land == {$i['name']}" . PHP_EOL;
                }
                if ( $i['name'] == $land ) {
                   $constant = strtoupper('continent_' . str_replace(' ','_',$continent)); 
                   if ( ! defined($constant) ) {
                        die("$constant is not defined?");
                   }
                   $i['continent'] = $constant;
                }
            }
        }
        if ( empty($i['continent']) ) {
            echo "Failed to find continent for: {$i['name']}" . PHP_EOL;
            var_dump($i['name']);
        }
    }
}

