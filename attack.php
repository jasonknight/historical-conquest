<?php
namespace HistoricalConquest;
function attack_player(
    $game_id,
    $attacker_id,
    $defender_id,
    $src_ext_id,
    $attacker_land_ext_id,
    $defender_land_ext_id,
    &$messages,
    &$errors
) {
    global $wpdb;
    $players = get_players($game_id,[$attacker_id,$defender_id],$errors);
    $state = new \stdClass;
    $state->attacker = null;
    $state->defender = null;
    foreach ( $players as $p ) {
        if ( $p->id == $attacker_id ) {
            $state->attacker = $p;
        }
        if ( $p->id == $defender_id ) {
            $state->defender = $p;
        }
    }
    if ( is_null($state->attacker) ) {
        $errors[] = "Attacker is null";
        $result['errors'] = $errors; 
        send_json($result);
        exit;
    }
    if ( is_null($state->defender) ) {
        $errors[] = "Defender is null";
        $result['errors'] = $errors; 
        send_json($result);
        exit;
    }
    $src_card_def = get_card_def($src_ext_id);
    $attacking_land_def = get_card_def($attacker_land_ext_id);
    $defending_land_def = get_card_def($defender_land_ext_id);

    $cards_involved = get_attack_cards_involved($state->attacker,$src_ext_id);
    action_log("There are " . count($cards_involved) . " card involved in the attack");
    if ( count($cards_involved) === 0 ) {
        $msg = "There are no cards involved in the attack, so it's over!";
        $messages[] = $msg;
        return;
    }
    $defs = array_map(function ($id) {
        return get_card_def($id); 
    },$cards_involved);
    $attack = 0;
    foreach ( $defs as $def ) {
        $attack = $attack + intval($def->strength);
    }
    $state->attacker->armies = array_filter($defs,function ($d) {
        return preg_match('/ARMY/',type_to_name($d->maintype)); 
    });
    $state->attacker->leaders = array_filter($defs,function ($d) {
        return preg_match('/LEADER/',type_to_name($d->maintype)); 
    });
    $state->attacker->spiritual_leaders = array_filter($defs,function ($d) {
        return preg_match('/SPIRITUAL_LEADER/',type_to_name($d->maintype)); 
    });
    $abilities_involved = get_attack_abilities_involved($state->attacker,$src_ext_id);
    foreach( $abilities_involved as $a ) {
        if ( in_array($a->affects_attribute,['strength','attack']) ) {
            $attack += intval($a->affect_amount);
        }
    }
    action_log("Final Calculated attack: " . $attack);

    // Step 2, process the defender
    $d_cards_involved = get_attack_cards_involved($state->defender,$defender_land_ext_id);
    action_log("There are " . count($d_cards_involved) . " cards involved in the defense");
    if ( count($d_cards_involved) === 0 ) {
        $msg = "There are no cards involved in the defense, so it's over!";
        $messages[] = $msg;
        return;
    }
    $d_defs = array_map(function ($id) {
        return get_card_def($id); 
    },$d_cards_involved);
    
    $state->defender->armies = array_filter($d_defs,function ($d) {
        return preg_match('/ARMY/',type_to_name($d->maintype)); 
    });
    $state->defender->leaders = array_filter($d_defs,function ($d) {
        return preg_match('/LEADER/',type_to_name($d->maintype)); 
    });
    $state->defender->spiritual_leaders = array_filter($d_defs,function ($d) {
        return preg_match('/SPIRITUAL_LEADER/',type_to_name($d->maintype)); 
    });
    $defense = 0;
    foreach ( $d_defs as $d_def ) {
        $defense += intval($d_def->strength);
    }
    $d_abilities_involved = get_attack_abilities_involved($state->defender,$defender_land_ext_id);
    foreach( $d_abilities_involved as $a ) {
        // Some abilities only affect a certain round
        if ( in_array($a->affects_attribute,['strength','defense']) ) {
            $defense += intval($a->affect_amount);
        }
    }
    action_log("Final Calculated defense: " . $defense);

    // Attila Check
    $attila_present = array_filter($cards_involved,function ($c) { return $c === 'C04201'; });
    if ( $attila_present ) {
       $old_attack = $attack;
       $attack = $attack * 2;
       $messages[] = "Attila doubles your attack strength from " . number_format($old_attack) . " to " . number_format($attack). "."; 
    }
    $satomura_present = count(array_filter($cards_involved,function ($c) { $c === 'AU4202'; })) > 0;
    if ( $satomura_present ) {
        $chars = array_filter($defs,function ($d) {
            return $d->ext_id != 'AU4202' && preg_match('/CHARACTER/',type_to_name($d->maintype)); 
        }); 
        if ( count($chars) > 0 ) {
            $max_card = null;
            foreach ( $chars as $c ) {
                if ( $max_card === null ) {
                    $max_card = $c;
                }
                if ( $max_card->strength < $c->strength ) {
                    $max_card = $c;
                }
            }
            if ( $max_card ) {
                // This card is already counted once,
                // so this is effectively doubling
                $attack += intval($max_card->strength);
                $messages[] = "Due to training from Satomura Joha, " . $max_card->name . " adds double their strength to the attack.";
            }
        }
    }
    // No for the defender 
    $satomura_present = count(array_filter($d_cards_involved,function ($c) { $c === 'AU4202'; })) > 0;
    if ( $satomura_present ) {
        $chars = array_filter($d_defs,function ($d) {
            return $d->ext_id != 'AU4202' && preg_match('/CHARACTER/',type_to_name($d->maintype)); 
        }); 
        if ( count($chars) > 0 ) {
            $max_card = null;
            foreach ( $chars as $c ) {
                if ( $max_card === null ) {
                    $max_card = $c;
                }
                if ( $max_card->strength < $c->strength ) {
                    $max_card = $c;
                }
            }
            if ( $max_card ) {
                // This card is already counted once,
                // so this is effectively doubling
                $defense += intval($max_card->strength);
                $messages[] = "Due to training from Satomura Joha, " . $max_card->name . " adds double their strength to the defense.";
            }
        }
    }
    $winner = null;
    $loser = null;
    $loser_defs = [];
    $loser_cards = [];
    $loser_land_id = '';
    $messages[] = $state->defender->name . " has a defensive strength of " . number_format($defense);
    $messages[] = $state->attacker->name . " has a attack strength of " . number_format($attack);
    if ( $attack > $defense ) {
        $winner = $state->attacker;
        $loser = $state->defender;
        $loser_defs = $d_defs;
        $loser_cards = $d_cards_involved;
        $loser_land_id = $defender_land_ext_id;
        $messages[] = $winner->name . " is victorious.";
    } else if ( $defense > $attack ) {
        $winner = $state->defender;
        $loser = $state->attacker;
        $loser_defs = $defs;
        $lost_cards = $cards_involved;
        $loser_land_id = $attacker_land_ext_id;
        $messages[] = $winner->name . " is victorious.";
    } else {
        $messages[] = "Attack and Defense are equal, no clear victory";
    }
    if ( $loser ) {
        $rc = get_row_col_for($loser,$loser_land_id);
        if ( $rc ) {
            $ar = $loser->damagemat[$rw->row][$rc->col];
            if ( ! is_array($ar) ) {
                $ar = [];
            }
            array_push($ar,['morale',-100]);
            $loser->damagemat[$rw->row][$rc->col] = $ar;
            $sql = "UPDATE `hc_players` SET damagemat = %s WHERE id = %d";
            $sql = $wpdb->prepare($sql,json_encode($loser->damagemat),$loser->id);
            $wpdb->query($sql);
            if ( !empty($wpdb->last_error) ) {
                action_log($sql);
                action_log($wpdb->last_error);
                $errors[] = "Failed to update damagemat";
                return;
            }
        } // if rc
        $to_discard = null;
        $discardables = array_filter($loser_defs,function ($ld) {
            return !preg_match('/LAND/',type_to_name($ld->maintype)) && !is_active_area_card($ld); 
        });
        foreach ( $discardables as $discardable ) {
            if ( $to_discard === null )
                $to_discard = $discardable;
            if ( intval($to_discard->strength) > intval($discardable->strength) ) 
                $to_discard = $discardable;
        }
        if ( $to_discard ) {
            $messages[] = $loser->name . " has lost the battle and " . $to_discard->name . " is mourned!";
            $loser = system_discard($loser,$to_discard->ext_id);
            $sql = "UPDATE `hc_players` SET discardpile = %s, playmat = %s, abilitymat = %s WHERE id = %d";
            $sql = $wpdb->prepare(
                $sql, 
                json_encode($loser->discard_pile),
                json_encode($loser->playmat), 
                json_encode($loser->abilitymat),
                $loser->id
            );
            $wpdb->query($sql);
            if ( !empty($wpdb->last_error) ) {
                action_log($sql);
                action_log($wpdb->last_error);
                $errors[] = "Failed to force discard";
                return;
            }
        }
    }

}
function get_attack_abilities_involved($p,$id) {
    $rc = get_row_col_for($p,$id);
    $mat = $p->abilitymat;
    $abs = [];
    for ( $row = 0; $row < count($mat); $row++) {
        for ( $col = 0; $col < count($mat[$row]); $col++ ) {
            if ( !is_array($mat[$row][$col]) ) 
                continue;
            $ar = $mat[$row][$col];
            // fix cause we made errors in insertion that have been fixed
            if ( isset($ar['id']) ) {
                $ar = [$ar];
            }
            $ar = array_map(function ($m) {
                return mat_item_to_ability($m); 
            },$ar);
            foreach ($ar as $ab) {
                $scope = type_to_name($ab->apply_to_scope);
                if ( $col === $rc->col && $mat[$row][$col] !== 0 ) {
                    if ( 
                        preg_match('/ALWAYS_ON/',$scope) || 
                        preg_match('/ATTACK/',$scope) || 
                        preg_match('/APPLY_PLAYER/',$scope)
                    ) {
                        array_push($abs,$ab);
                    } 
                } else if ( preg_match('/APPLY_PLAYER/',$scope) ) {
                    array_push($abs,$ab);
                } 
            }
        }
    }
    return $abs;
}
function get_attack_cards_involved($p,$src_id) {
    action_log(__FUNCTION__ . " src_id = $src_id");
    $rc = get_row_col_for($p,$src_id);
    action_log(__FUNCTION__ . ", " . json_encode($rc));
    $mat = $p->playmat;
    $cards = [];
    for ( $row = 0; $row < count($mat); $row++) {
        for ( $col = 0; $col < count($mat[$row]); $col++ ) {
            if ( $col === $rc->col && $mat[$row][$col] !== 0 ) {
                array_push($cards,$mat[$row][$col]);
            } else if ( $mat[$row][$col] !== 0 ) {
                $def = get_card_def($mat[$row][$col]);
                if ( ! $def ) 
                    continue;
                if ( !is_active_area_card($def) )
                    continue;
                array_push($cards,$mat[$row][$col]);
            }
            
        }
    }
    return $cards;
}
