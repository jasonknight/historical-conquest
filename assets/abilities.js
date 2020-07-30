<?php 
namespace HistoricalConquest;
?>
function maybe_add_abilities(player,card_def,pdef) {
    _log('maybe add abilities',player,card_def,pdef);
    if ( card_def.abilities.length == 0 ) {
        return;
    }
    $.each(card_def.abilities,function () {
        let a = this; 
        if ( a.apply_to_scope == window.types.SCOPE_ALWAYS_ON ) {
            _log("Always on Ability",a);
            always_on_ability(player,card_def,pdef,a);
        }
    });  
}
function ability_to_mat_item(ability) {
    return {
        id: ability.id,
        charges: ability.charges,
    };
}
function mat_item_to_html(mat) {
    let a = mat_item_to_ability(mat);
    let d = _div(null,'abilitymat-item');
    let bits = [];
    bits.push( 'id:' + mat.id );
    bits.push( 'charges:' + mat.charges );
    bits.push( 'desc:' + a.description );
    d.html(bits.join(',<br />'));
    return d;
}
function mat_item_to_ability(m) {
    for ( let key in window.carddb ) {
        let card = window.carddb[key];
        for ( let i = 0; i < card.abilities.length; i++ ) {
            let a = card.abilities[i];
            if ( a.id == m.id ) {
                return a;
            }
        }
    }
    return {};
}
// Here we handle always on abilities, these are usually
// buffs to an attribut
function always_on_ability(player,cdef,pdef,ability) {
    // APPLY_PLAYER means it has no real criteria for application
    // it just applies to 
    if  ( ability.apply_to_type == window.types.APPLY_PLAYER ) {
        let mat = ability_to_mat_item(ability);
        play_ability(player,mat,pdef.y,pdef.x);
    }
}
function play_ability(player,mat,row,col) {
    let abs = player.abilitymat[row][col];
    if ( ! Array.isArray(abs) ) {
        abs = [];
    }
    for ( let i = 0; i < abs.length; i++ ) {
        if ( abs[i].id == mat.id ) {
            return;
        }
    }
    abs.push(mat);
    player.abilitymat[row][col] = abs;
}
