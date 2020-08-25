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
    let src_card_def = get_card_def(src.attr('card-id'));
    let land_card = get_attacking_land(player,src);
    let land_card_display = get_card(land_card.ext_id);
    let div = $('<div />');
    let heading = $('<h2 />');
        div.append(heading);
    heading.html(player.name);
    let table = $('<table align="center" class="attack-table"></table>');
    let row = $('<tr />');
    let hrow = row.clone();
    table.append(hrow);
    hrow.append($('<td colspan="3" class="table-heading"><h2>Choose a land to attack</h2></td>'));
    hrow.css({"height": "50px"});
    let lcol = $('<td />');
    let mcol = $('<td />');
        mcol.append('<p class="land-vs">VS</p>');
    let rcol = lcol.clone();
    row.append(lcol)
    row.append(mcol)
    row.append(rcol);
    table.append(row);
    lcol.append(div) 
    div.append(land_card_display);
    dialog.append(table);

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
        div.append(heading);
        $.each(op.attackables,function () {
            let cdiv = $('<div style="margin-bottom: 20px;"></div>');
            cdiv.append(this.card_display);
            div.append(cdiv);
        });
        rcol.append(div);
    });
}
function get_attacking_land(p,src) {
    let card_id = src.attr('card-id');
    let rc = get_row_col_for(p,card_id);
    let land_id = p.playmat[p.playmat.length -2][rc.col];
    return get_card_def(land_id);
}
