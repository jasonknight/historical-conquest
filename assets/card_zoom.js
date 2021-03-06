function add_card_controls(d,src,clone) {
    let button_row = _div('card_controls','card-controls zoom-holder-child');
    let cancel = _div(null,'button cancel-button'); 
    cancel.html('X');
    cancel.on('click',function () {
        $('body').trigger($.Event('close_zoom_holder'));
    });
    button_row.append(cancel);
    //maybe_add_explorer_controls(d,src,clone,button_row); 
    trigger_card_controls_create(d,src,clone,button_row);
    maybe_add_str_def_display(d,src,clone);
    $('body').append(button_row);
    button_row.css({
        position: 'absolute',
        left: d.offset().left,
        top: d.offset().top + d.height() + 5,
        'z-index': window.layers.popup2,
    });
} 
function get_card_zoom_holder(src,and_append) {
    $('body').trigger($.Event('close_zoom_holder'));
    let d = _div(null,'card-zoom-holder');
    if ( and_append ) {
        $('body').append(d);
    }
    let clone = get_card(src.attr('card-id'),true);
    let _left = src.offset().left;
    let _top = src.offset().top;
    let _w = src.width() * 4.2;
    let _h = src.height() * 2.7;
    let _pleft = _left - clone.width() / 2;
    let _ptop = _top - (clone.height() / 2) - 50;
    let cap = 0;
    while ( _pleft + _w >= $(window).width() ) {
        _pleft = _pleft - 50;
        if ( cap > 10 ) 
            break;
        cap++;
    }
    if ( _pleft < 0 ) {
        _pleft = 20;
    }
    cap = 0;
    while ( _ptop + _h >= $(window).height() ) {
        _ptop = _ptop - 50;
        if ( cap > 10 ) 
            break;
        cap++;
    }
    if ( _ptop < 0 ) {
        _ptop = 10;
    }
    d.css({
        "position": "absolute",
        "left": _pleft,
        "top": _ptop,
        "width": _w,
        "height": _h,
        "z-index": window.layers.popup,
    });
    clone.css({
        "width": _w * 0.98,
        "height": _h * 0.98
    });
    clone.on('click',function() {
        d.remove();
        trigger_close_zoom_holder(); 
    });
    clone.find('.illustration').find('img').css({"width": (clone.width() * 0.25) + 'px'});
    clone.find('.history-plate').html(get_card_summary(src.attr('card-id')));
    clone.find('.ability-plate').html(get_card_abilities(src.attr('card-id')));
    clone.find('.date-category').html(get_card_year(src.attr('card-id')));
    if ( can_play(src) ) {
        highlight_playable_squares_for(src);
    }
    $('table.grid td.highlight-square').on('click',function() {
        let id = src.attr('card-id');
        let p = get_current_player();
        let y = $(this).attr('y');
        let x = $(this).attr('x');
        play_card(p,id,y,x);
        trigger_close_zoom_holder();
        $('body').trigger($.Event('refresh_board'));
        d.remove();
        unhighlight_playable_squares();
    });
    d.append(clone);
    add_card_controls(d,src,clone);
    return d;
}
function maybe_add_abilities_button(d,src,clone,button_row) {
    let def = get_card_def(src.attr('card-id'));
    if ( ! def.abilities || def.abilities.length == 0 ) {
        return;
    }
    
    let ab = _div(null,'button abilities-button');
        ab.html("Abilities");
        ab.on('click',function () {
            _log("ab.on click");
            let e = $.Event('card_zoom_show_abilities');
            e.def = def;
            e.d = d;
            e.src = src;
            e.clone = clone;
            $('body').trigger(e);
        });
        button_row.append(ab);
}
function add_discard_button(d,src,clone,button_row) {
    let def = get_card_def(src.attr('card-id'));
    let ab = _div(null,'button discard-button');
        ab.html("Discard");
        ab.on('click',function () {
            let e = $.Event('card.discard');
            e.def = def;
            e.d = d;
            e.src = src;
            e.clone = clone;
            $('body').trigger(e);
            trigger_close_zoom_holder();
        });
        button_row.append(ab);
}
function convert_to_abilities_widget(d,src,clone) {
    let p = get_current_player();
    let def = get_card_def(src.attr('card-id'));
    d.find('.card').html('');
    d.find('.card').unbind('click');
    let h = _div(null,'card-ability-desc');
    let tbl = $('<table />');
        h.append(tbl);
    for ( let i = 0; i < def.abilities.length; i++ ) {
        let row = $('<tr />');
            tbl.append(row);
        let a = def.abilities[i];
        let col1 = $('<td class="is-active" />');
        let btn = _div(null,'button activate-ability');
            if ( a.apply_to_scope == window.types.SCOPE_ALWAYS_ON) {
                btn.html("Always");
                btn.addClass('ability-always');
                btn.attr('disabled',true);
            } else if ( a.apply_to_scope == window.types.SCOPE_ANYTIME) {
                btn.html("Activate");
                btn.on('click',function () {
                    trigger_activate_ability(d,src,clone,a);
                });
            } else if ( a.apply_to_scope == window.types.SCOPE_TURN) {
                btn.html("Turn");
            } else if ( a.apply_to_scope == window.types.SCOPE_ATTACK) {
                btn.html("Attack");
            } else if ( a.apply_to_scope == window.types.SCOPE_DEFENSE) {
                btn.html("Defense");
            } else {
                btn.html("Activate");
            }
            
            col1.append(btn);
            row.append(col1);
        let col2 = $('<td class="desc" />');
            row.append(col2);
        col2.html(a.description);
    }
    d.find('.card').append(h);
}
