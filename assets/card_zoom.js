function add_card_controls(d,src,clone) {
    let button_row = _div('card_controls','card-controls zoom-holder-child');
    let cancel = _div(null,'button cancel-button'); 
    cancel.html('X');
    cancel.on('click',function () {
        $('body').trigger($.Event('close_zoom_holder'));
    });
    button_row.append(cancel);
    maybe_add_explorer_controls(d,src,clone,button_row); 
    maybe_add_str_def_display(d,src,clone);
    $('body').append(button_row);
    button_row.css({
        position: 'absolute',
        left: d.offset().left,
        top: d.offset().top + d.height() + 5
    });
} 
function get_card_zoom_holder(src,and_append) {
    $('body').trigger($.Event('close_zoom_holder'));
    let d = _div(null,'card-zoom-holder');
    if ( and_append ) {
        $('body').append(d);
    }
    let clone = src.clone();
    let _left = src.offset().left;
    let _top = src.offset().top;
    let _w = src.width() * 2;
    let _h = src.height() * 2;
    let _pleft = _left - src.width() / 2;
    let _ptop = _top - (src.height() / 2) - 50;
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
        "z-index": window.popup_layer,
    });
    clone.css({
        "width": _w * 0.98,
        "height": _h * 0.98
    });
    clone.on('click',function() {
        d.remove();
        $('body').trigger($.Event('close_zoom_holder'));
        
    });
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
