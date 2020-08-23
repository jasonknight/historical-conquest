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

}
