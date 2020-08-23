function get_column_defense_amount(mat,col) {
    let def_amount = 0;
    let row = mat.length - 3; 
    while ( row > -1 ) {
       let card = window.carddb[ mat[row][col] ];
        if ( card ) {
            if ( isNaN(parseInt(card.defense_strength) ) ) {
                row--;
                continue;
            }
            def_amount = def_amount + parseInt(card.defense_strength); 
        }
        row--;
    }
    return def_amount;
}
function maybe_add_str_def_display(d,src,clone) {
    if ( is_land_row_card(src) ) {
        let def_div = _div('def_display');
        let id = src.attr('card-id');
        let def_amount = 0;
        let col = parseInt(src.parent().attr('x'));
        let mat = get_current_player().playmat;
        def_amount = get_column_defense_amount(mat,col); 
        def_div.html(def_amount);
        $('body').append(def_div);
        def_div.addClass('defense-display');
        def_div.addClass('zoom-holder-child');
        def_div.css({
            position: 'absolute',
            left: (d.offset().left + d.width()) - 100,
            top: d.offset().top - def_div.height(),
            'z-index': window.layers.popup,
        });
    }
}
