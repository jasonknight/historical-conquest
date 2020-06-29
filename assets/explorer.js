function get_transportable(src) {
    let col = parseInt(src.parent().attr('x'));
    let p = get_current_player();
    let transportable = [];
    for ( let row = 0; row < p.playmat.length - 2; row++ ) {
        let cid = p.playmat[row][col];
        if ( cid == 0 )
            continue;
        let cdef = window.carddb[cid];
        if ( cdef && ['character','army'].indexOf(cdef.type) != -1 && cdef.id != src.attr('card-id') ) {
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
            let destinations = get_transport_destinations();
            let p = get_current_player();
            if ( !contains_card_type(transportable,'army') ) {
                let new_dests = [];
                for ( let i = 0; i < destinations.length; i++ ) {
                    if ( destinations[i].player.id != p.id ) 
                        continue;
                    new_dests.push(destinations[i]);
                }
                destinations = new_dests;
            }        
            convert_to_destination_widget(d,src,clone,holder,destinations);
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
            console.log("transporting to: ",card.attr('card-id'));
        });
    }
}
