<?php 
namespace HistoricalConquest;
?>
function maybe_add_attack_controls(d,src,clone,button_row) {
    let def = get_card_def(src.attr('card-id'));
    let morale = get_player_morale(get_current_player()); 
    if ( morale < 800 ) {
        _log("player morale is < 800");
        return;
    }
    if ( get_current_player().hand.indexOf( src.attr('card-id') ) != -1 ) {
        _log("can't attack from the hand")
        return;
    }
    if ( ! type_to_name(def.maintype).match(/LAND/) && !type_to_name(def.maintype).match(/EXPLORER/) ) {
        _log("attack controls just for explorer and land");
        return;
    }
    let ab = _div(null,'button attack-button');
        ab.html("Attack");
        ab.on('click',function () {
            console.log("attack.on click");
            let e = $.Event('card_zoom.show_attack_options');
            e.def = def;
            e.d = d;
            e.src = src;
            e.clone = clone;
            $('body').trigger(e);
        });
        button_row.append(ab);
}
function show_attack_options(player,d,src) {
    _log('in show_attack_options');
    let dialog = create_dialog('attack_options');
    let src_card_def = get_card_def(src.attr('card-id'));
    let land_card = get_attacking_land_def(player,src);
    let land_card_display = get_card(land_card.ext_id);
    let table = $('<table class="attack-table" />'); 
    let hrow = $('<tr />');
        hrow.append('<h2>Choose a land to attack</h2>');
    table.append(hrow);
    dialog.find('.dialog-body').append(table);
    // Get enemy lands
    let attackables = [];
    $.each(window.board.players,function () {
        if ( this.id == get_current_player().id ) 
            return;
        let mat = this.playmat;
        let row = mat.length - 2;
        let op = {};
            op.player = this;
            op.attackables = [];
        for ( let col = 0; col < mat[row].length - 2; col++ ) {
            if ( mat[row][col] != 0 ) {
                let en = {};
                en.card_id = mat[row][col];
                en.card_def = get_card_def(en.card_id);
                // So if they launch an attack from an Explorer they can
                // attack anywhere
                if ( 
                        en.card_def.continent != land_card.continent && 
                        !type_to_name(src_card_def.maintype).match(/EXPLORER/) 
                ) { 
                    continue;
                }
                en.card_display = get_card(en.card_id);
                en.card_display.unbind('click');
                en.card_display.on('click',function () {
                    trigger_close_dialog($(this),dialog);
                    trigger_attack(player,op.player,src.attr('card-id'),land_card_display.attr('card-id'),en.card_id);
                });
                op.attackables.push(en);
            }
        }
        attackables.push(op);
    });
    $.each(attackables,function () {
        let op = this;
        let div = $('<div />');
        let heading = $('<h2 />');
        heading.html(this.player.name);
        $.each(op.attackables,function () {
            let cdiv = $('<div style="float: left;margin-bottom: 10px;margin-right: 10px;"></div>');
            cdiv.append(this.card_display);
            div.append(cdiv);
        });
        let row = $('<tr />');
        let col = $('<td />');
            col.append(heading);
        row.append(col);
        table.append(row);
        row = $('<tr />');
        col = $('<td />');
        row.append(col);
        col.append(div);
        table.append(row);
    });
}
function get_attacking_land_def(p,src) {
    let card_id = src.attr('card-id');
    let rc = get_row_col_for(p,card_id);
    let land_id = p.playmat[p.playmat.length -2][rc.col];
    return get_card_def(land_id);
}
function get_remaining_attacks(p) {
   return p.attacks + "/" + p.max_attacks; 
}
function show_attack_dialog(attacker,defender,src_ext_id,attacker_land_ext_id,defender_land_ext_id) {
    let dialog = create_dialog('attack_dialog');
    dialog.attr('current-round',0);
    let attacking_land_display = get_card(attacker_land_ext_id,false);
        attacking_land_display.unbind('click');
    let defending_land_display = get_card(defender_land_ext_id,false);
        defending_land_display.unbind('click');
    let attack_button = get_generic_button("Attack " + get_remaining_attacks(attacker));
        attack_button.addClass('initiate-attack-button');
    attack_button.insertBefore(dialog.find('.dialog-body'));
    let tbl = $('<table />');
    attack_button.on('click', function () {
        tbl.find('tr.attack-msg-row').hide();
        if ( attacker.attacks <= 0 ) {
            trigger_attack_message("You have no more attacks!");
            return;
        }
        attacker.attacks = attacker.attacks - 1;
        $(this).html("Attack " + get_remaining_attacks(attacker));
        handle_attack(attacker,defender,src_ext_id,attacker_land_ext_id,defender_land_ext_id);
    });
    place_button(dialog,attack_button,'center','bottom');
    tbl.append('<tr class="name-row"/>');
    tbl.append('<tr class="card-row"/>');
    tbl.find('tr.name-row').append('<td>'+attacker.name+'</td>');
    tbl.find('tr.name-row').append('<td>&nbsp;</td>');
    tbl.find('tr.name-row').append('<td>'+defender.name+'</td>');
    tbl.find('tr.card-row').append('<td class="attacker" />');
    tbl.find('tr.card-row').append('<td class="vs-col">VS</td>');
    tbl.find('tr.card-row').append('<td class="defender" />');
    tbl.find('.attacker').append(attacking_land_display);
    tbl.find('.defender').append(defending_land_display);
    $('body').on('attack.msg',function (e) {
        let row = $('<tr class="attack-msg-row"/>');
        let td = $('<td colspan="3" class="attack-msg" />');
        td.html(e.msg);
        row.append(td);
        tbl.append(row);
    });
    let tbl_div = $('<div class="attacker-defender-table-holder" />');
    tbl_div.append(tbl);
    tbl_div.css({"margin-left": "auto", "margin-right": "auto"});
    dialog.find('.dialog-body').append(tbl_div);
    create_adjuster(function () {
        tbl_div.css({"width": tbl.outerWidth() + "px"});
    },50,100);

}
function handle_attack(attacker,defender,src_ext_id,attacker_land_ext_id,defender_land_ext_id){
    _log("ATTACK!",attacker,defender,src_ext_id);
    let state = {};
    state.attacker = {};
    state.defender = {};
    let src_card_def = get_card_def(src_ext_id);
    let attacking_land_def = get_card_def(attacker_land_ext_id);
    let defending_land_def = get_card_def(defender_land_ext_id);

    // Step 1, we need to know the attack points of p1
    let cards_involved = get_attack_cards_involved(attacker,src_ext_id);
    if ( cards_involved.length == 0 ) {
        trigger_attack_message("There are no cards involved in the attack!");
        return;
    }
    // Calculate the Attack
    let defs = cards_involved.map(function (id) {
        return get_card_def(id);
    });
    _log("defs:",defs);
    let attack = 0;
    defs.forEach(function (def) {
        // Note, we call it strength as an attr on the
        // card, but attack as an attr modifier
        attack = attack + parseInt(def.strength);
    });
    state.attacker.armies = defs.filter(function (d) { return type_to_name(d.maintype).match(/ARMY/); });
    state.attacker.leaders = defs.filter(function (d) { return type_to_name(d.maintype).match(/LEADER/); });
    state.attacker.spiritual_leaders = defs.filter(function (d) { return type_to_name(d.maintype).match(/SPIRITUAL_LEADER/); });
    let abilities_involved = get_attack_abilities_involved(attacker,src_ext_id);
    _log('abilities_involved=', abilities_involved);
    abilities_involved.forEach(function (a) {
        if ( ['strength','attack'].indexOf(a.affects_attribute) != -1 ) {
            _log('attack','ability=' + a.id, a.affects_attribute + '=' + a.affect_amount);
            // TODO: Some abilities only apply on the first round
            attack += parseInt(a.affect_amount);
        }
    });
    _log("Attack Cards Involved",cards_involved,defs,"Attack: " + attack);
    trigger_attack_message(attacker.name + " attack strength is " + attack);

    // Step 2, we need to know the defense points of p2
    let d_cards_involved = get_attack_cards_involved(defender,defender_land_ext_id);
    if ( d_cards_involved.length == 0 ) {
        trigger_defense_message("There are no cards involved in the defense!");
        return;
    }
    
    
    // Calculate the defense
    let d_defs = d_cards_involved.map(function (id) {
        return get_card_def(id);
    });
    state.defender.armies = d_defs.filter(function (d) { return type_to_name(d.maintype).match(/ARMY/); });
    state.defender.leaders = d_defs.filter(function (d) { return type_to_name(d.maintype).match(/LEADER/); });
    state.defender.spiritual_leaders = d_defs.filter(function (d) { return type_to_name(d.maintype).match(/SPIRITUAL_LEADER/); });
    _log("defs:",d_defs);
    _log("State",state);
    // Ability of CO4202 Gaius Julius Caesar assasinates a leader in the opposing camp before
    // the attack if an army is preset.
    if ( state.attacker.armies.length > 0 && state.defender.leaders.length > 0 ) {
        let has_julius = cards_involved.filter(function (ext_id) { return ext_id === 'CO4202'; }).length > 0;
        if ( has_julius ) {
            // okay, we have an army, they have a leader, we have Gaius, so someone's gotta die
            // we'll need to remove the assassinated card
            let vic = state.defender.leaders.pop();
            let army = state.attacker.armies[0];
            d_cards_involved = d_cards_involved.filter(function ( ext_id ) { return ext_id != vic.ext_id; });
            d_defs = d_defs.filter(function (d) { return d.ext_id != vic.ext_id; });
            defender.discard_pile.push(vic.ext_id);
            system_discard(defender,vic);
            trigger_attack_message("Inspired by Caesar, " + army.name + " successfully assasinates " + vic.name);
        }
    }
    let defense = 0;
    d_defs.forEach(function (def) {
        // Note, we call it strength as an attr on the
        // card, but defense as an attr modifier
        defense = defense + parseInt(def.strength);
    });
    

    let d_abilities_involved = get_defense_abilities_involved(defender,defender_land_ext_id);
    _log('abilities_involved=', d_abilities_involved);
    d_abilities_involved.forEach(function (a) {
        if ( ['strength','defense'].indexOf(a.affects_attribute) != -1 ) {
            _log('defense','ability=' + a.id, a.affects_attribute + '=' + a.affect_amount);
            // TODO: Some abilities only apply on the first round
            defense += parseInt(a.affect_amount);
        }
    });
    _log("Defense Cards Involved",d_cards_involved,defs,"defense: " + defense);
    trigger_attack_message(defender.name + " defense strength is " + defense);
    // TODO: Abilities that win attacks should trigger here
    // Ability of C04201 Attila the hun, doubles the attack if it's the first round
    let attila_present = cards_involved.filter(function (ext_id) { return ext_id == 'C04201'; }).length > 0;
    _log("Attila check", attila_present, attacker.attacks, attack.max_attacks);
    if ( attila_present && attacker.attacks == (attacker.max_attacks - 1) ) {
        trigger_attack_message("Attila doubles your attack strength from " + attack + " to " + (attack * 2));
        attack = attack * 2;
    }
    
    let winner = null;
    let loser = null;
    if ( attack > defense ) {
        winner = attacker;
        loser = defender;
        loser_land_id = defender_land_ext_id;
        trigger_attack_message(attacker.name + " wins with the stronger attack");
    }
    if ( defense > attack ) {
        winner = defender;
        loser = attacker;
        loser_land_id = attacker_land_ext_id;
        trigger_attack_message(defender.name + " wins with the stronger defense");
    }
    // Step 3 The loser has to lose 100 morale
    let rc = get_row_col_for(loser,loser_land_id);
    for ( let i = 0; i < window.board.players.length; i++ ) {
        if ( window.board.players[i].id != loser.id ) {
            continue;
        }
        let ar = window.board.players[i].damagemat[rc.row][rc.col];
        if ( !Array.isArray(ar) ) {
            ar = [];
        }
        ar.push(['morale',-100]);
        window.board.players[i].damagemat[rc.row][rc.col] = ar;
        render_players_damagemat(window.board.players);
    }

}

function get_attack_abilities_involved(p,src_id) {
    let rc = get_row_col_for(p,src_id);
    let mat = p.abilitymat;
    let abs = [];
    for ( let row = 0; row < mat.length - 1; row++ ) {
        for ( let col = 0; col < mat[row].length; col++ ) {
            // TODO: maybe implicate event cards?
            // which is why we're doing a full pass and not
            // just the column
            if ( !Array.isArray(mat[row][col]) ) {
                continue;
            }
            let ar = mat[row][col];
            ar = ar.map(function (m) { return mat_item_to_ability(m); });
            ar.forEach(function (ab) {
                let scope = type_to_name(ab.apply_to_scope);
                if ( col == rc.col && mat[row][col] != 0 ) {
                    if ( scope.match(/ALWAYS_ON/) || scope.match(/ATTACK/) ) {
                       abs.push(ab);
                    }  
                } else if (scope.match(/APPLY_PLAYER/)) {
                    abs.push(ab);
                }
            });
            
        }
    }
    return abs;
}
function get_defense_abilities_involved(p,src_id) {
    let rc = get_row_col_for(p,src_id);
    let mat = p.abilitymat;
    let abs = [];
    for ( let row = 0; row < mat.length - 1; row++ ) {
        for ( let col = 0; col < mat[row].length; col++ ) {
            // TODO: maybe implicate event cards?
            // which is why we're doing a full pass and not
            // just the column
            if ( !Array.isArray(mat[row][col]) ) {
                continue;
            }
            let ar = mat[row][col];
            ar = ar.map(function (m) { return mat_item_to_ability(m); });
            ar.forEach(function (ab) {
                let scope = type_to_name(ab.apply_to_scope);
                if ( col == rc.col && mat[row][col] != 0 ) {
                    if ( scope.match(/ALWAYS_ON/) || scope.match(/DEFENSE/) ) {
                       abs.push(ab);
                    }  
                } else if (scope.match(/APPLY_PLAYER/)) {
                    abs.push(ab);
                }
            });
        }
    }
    return abs;
}
function get_attack_cards_involved(p,src_id) {
    let rc = get_row_col_for(p,src_id);
    let mat = p.playmat;
    let cards = [];
    for ( let row = 0; row < mat.length - 1; row++ ) {
        for ( let col = 0; col < mat[row].length; col++ ) {
            // TODO: maybe implicate event cards?
            // which is why we're doing a full pass and not
            // just the column
            if ( col == rc.col && mat[row][col] != 0 ) {
               cards.push(mat[row][col]); 
            }
        }
    }
    return cards;
}
function dam_item_to_html(mat) {
    let d = _div(null,'damagemat-item');
    let bits = [];
    bits.push( 'attr:' + mat[0]);
    bits.push( 'amount:' + mat[1] );
    d.html(bits.join(',<br />'));
    return d;
}
