function get_transportable(src) {
    let col = parseInt(src.parent().attr('x'));
    let p = get_current_player();
    let transportable = [];
    for ( let row = 0; row < p.playmat.length - 2; row++ ) {
        let cid = p.playmat[row][col];
        if ( cid == 0 )
            continue;
        let cdef = window.carddb[cid];
        if ( cdef && (is_character(cdef.maintype) || is_army(cdef.maintype)) && cdef.ext_id != src.attr('card-id') ) {
            transportable.push(cid);
        }
    }
    return transportable;
}
function get_transport_destinations() {
    let destinations = [];
    for ( let i = 0; i < window.board.players.length; i++ ) {
        let p = window.board.players[i];
        let land_row = p.playmat.length - 2;
        let mat = p.playmat[land_row];
        for ( let j = 0; j < mat.length; j++) {
            if ( window.carddb[mat[j]] ) {
                let dest = {};
                dest.player = p;
                dest.id = mat[j];
                destinations.push(dest);
            }
        }
    }
    return destinations;
}
function convert_to_transport_widget(d,src,clone) {
    let transportable = get_transportable(src); 
    console.log('transportable',transportable);
    if ( transportable.length == 0 )
        return;
    d.html('');
    get_current_player().transport = [];
    for ( let i = 0; i < transportable.length; i++ ) {
        let card = get_card(transportable[i]);
        card.unbind('click');
        card.css('width',(d.width() / 2) * 0.90 );
        card.css('height',(d.height() / 2) * 0.90);
        let holder = _div(null,'transport-holder');
        holder.append(card);
        holder.css('width',(d.width() / 2) * 0.90 );
        holder.css('height',(d.height() / 2) * 0.90);
        d.append(holder);
        holder.on('click',function () {
            if ( $(this).hasClass('transport-carry-active') ) {
                console.log("Removing transport-carry-active");
                $(this).removeClass('transport-carry-active');
                get_current_player().transporting = get_current_player().transporting.filter(function (id) {
                    return id != card.attr('card-id');
                });
                // remove from transport
                return;
            }
            let destinations = get_transport_destinations();
            let p = get_current_player();
            let def = get_card_def(src.attr('card-id'));
            $(this).addClass('transport-carry-active');
            get_current_player().transport.push(card.attr('card-id'));
            console.log("Appending ",card.attr('card-id'));
            if ( def.carry_capacity == 1 || get_current_player().transport.length == def.carry_capacity) {
                // I.E. we are splitting attack out, so always remove
                // other player cards
                if ( true ) {
                    let new_dests = [];
                    for ( let i = 0; i < destinations.length; i++ ) {
                        if ( destinations[i].player.id != p.id ) 
                            continue;
                        new_dests.push(destinations[i]);
                    }
                    destinations = new_dests;
                }        
                get_current_player().transport.push(src.attr('card-id'));
                convert_to_destination_widget(d,src,clone,holder,destinations);
            }
        });
    }
}
function convert_to_destination_widget(d,src,clone,origin_holder,destinations) {
    let p = get_current_player();
    if ( destinations.length == 0 )
        return;
    d.html('');
    for ( let i = 0; i < destinations.length; i++ ) {
        let card = get_card(destinations[i].id);
        card.unbind('click');
        card.css('width',(d.width() / 2) * 0.90 );
        card.css('height',(d.height() / 2) * 0.80);
        let holder = _div(null,'transport-holder');
        holder.append(card);
        holder.css('width',(d.width() / 2) * 0.90 );
        holder.css('height',(d.height() / 2) * 0.90);
        holder.append(
            $('<p class="land-owner-tag" />')
        );
        holder.find('.land-owner-tag').html(destinations[i].player.name);

        d.append(holder);
        holder.on('click',function () {
            let def = get_card_def(card.attr('card-id'));
            let dest = get_row_col_for(get_current_player(),card.attr('card-id'));
            let to_transport = get_current_player().transport; 
            while ( to_transport.length > 0 ) {
                let player = get_current_player();
                let card_to_play = to_transport.pop();
                let nrow = get_next_open_row(player,dest.row,dest.col);
                if ( ! nrow ) {
                    console.log("Could not determine next open row");
                    return;
                }
                player.hand.push(card_to_play);
                let old_dest = get_row_col_for(player,card_to_play);
                player.playmat[old_dest.row][old_dest.col] = 0;
                unadvance_move();
                play_card(player,card_to_play,nrow,dest.col);
            }
            advance_move();
            trigger_refresh();
            trigger_close_zoom_holder();
        });
    }
}
function maybe_add_explorer_controls(d,src,clone,button_row) {
    if ( 
                src.hasClass('card-type-explorer') && 
                get_current_player().hand.indexOf(src.attr('card-id')) == -1 && 
                get_transportable(src).length != 0
    ) {
       let transport = _div(null,'button transport-button'); 
        transport.html('Transport');
        transport.on('click',function () {
            convert_to_transport_widget(d,src,clone);
        });
        button_row.append(transport);
    } else {
        console.log(
            "no transport",
            src.attr('card-id'),
            src.hasClass('card-type-explorer'),
            get_current_player().hand.indexOf(src.attr('card-id')) == -1,
            get_transportable(src)
        );
    }
    return button_row;
}
