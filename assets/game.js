(function ($) {
    let panels = {};
        panels.data = {};
        panels.data.card_width = 0.90;
        panels.data.card_height = 0.85
    function _div(id,kls) {
        let d = null;
        if ( id ) {
            d = $('<div id="' + id + '" />');
        } else {
            d = $('<div />');
        }
        if ( kls )
            d.addClass(kls);
        return d;
    }
    function _tab_button(id,player) {
        let d = _div(id + '_tab_button', 'tab-button');
        d.on('click',function () {
            $('.tab-button').removeClass('active-tab');
            $('div.tab').hide();
            $('#' + id).show();
            $(this).addClass('active-tab');
        });
        d.html(player.name);
        return d;
    }
    function get_base_table() {
        return [
            [0,0,0,0,0],
            [0,0,0,0,0],
            [0,0,0,0,0],
            [0,0,0,0,0],
            [0,0,0,0,0],
        ];
    }
    function get_grid_column_width() {
        return (panels.main.width() / get_base_table()[0].length);
    }
    function get_card_column_width() {
        return panels.data.grid_width * panels.data.card_width;
    }
    function get_grid_column_height() {
        let h = $(window).height() * 0.85;
        return (h/get_base_table().length);
    }
    function get_card_column_height() {
        return panels.data.grid_height * panels.data.card_height;
    }
    function get_land_pile() {
        let d = _div(null,null);
        d.addClass('land-pile');
        return d;
    }
    function get_card_zoom_holder(src) {
        let d = _div(null,'card-zoom-holder');
        let clone = src.clone();
        let _left = src.offset().left;
        let _top = src.offset().top;
        let _w = src.width() * 2;
        let _h = src.height() * 2;
        let _pleft = _left - src.width() / 2;
        let _ptop = _top - src.height() / 2;
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
            "height": _h
        });
        clone.css({
            "width": _w * 0.98,
            "height": _h * 0.98
        });
        clone.on('click',function() {
            d.remove();
        });
        d.append(clone);
        return d;
    }
    function get_card(c) {
        let card = $($('div.card-template').html()) ;
        card.css({
            "width": get_card_column_width() + 'px',
            "height": get_card_column_height() + 'px',
        });
        card.on('click',function () {
            $('div.card-zoom-holder').remove();
            let cont = get_card_zoom_holder($(this));
            $('body').append(cont);
        });
        return card;
    }
    function render_card(table,card,row,col) {
        if (table[row][col].append) {
            table[row][col].append(card);
        }
        if (table[row][col] == 0) {
            table[row][col] = card;
        }
        
    }
    function render_hand(player,rows_cols) {
        let rl = rows_cols.length;
        let cl = rows_cols[0].length;
        let last_row = rows_cols.length - 1;
        let i = cl - 1;
        let j = last_row;
        $.each(player.hand,function () {
           let card = get_card(this);  
            render_card(rows_cols,card,i,j);
            j--;
        });
    }
    function render_play_mat(player,target) {
        let rows_cols = player.playmat;
        let land_pile = _div(player.id + '_land_pile','pile land-draw-pile');
        let draw_pile = _div(player.id + '_draw_pile','pile draw-pile');
        let discard_pile = _div(player.id + '_discard_pile','pile discard-pile');
        let rl = rows_cols.length;
        let cl = rows_cols[0].length;
        rows_cols[rl-2][cl-1] = land_pile;
        rows_cols[rl-2][cl-2] = draw_pile;
        rows_cols[rl-3][cl-1] = discard_pile;
        let tcard = get_card();
        render_card(rows_cols,tcard,1,1);
        let table = $('<table></table>');
            table.addClass('grid');
        let last_row = rows_cols.length - 1;
        for ( let col = rows_cols[0].length - 1; col >= 0; col-- ) {
           if ( rows_cols[last_row-1][col] == 0 ) {
                let _lp = get_land_pile();
                rows_cols[last_row-1][col] = _lp;
           }
        }
        render_hand(player,rows_cols);
        for ( let row = 0; row < rows_cols.length; row++) {
            let tr = $('<tr />');
            for ( let col = 0; col < rows_cols[0].length; col++ ) {
                let td = $('<td />');
                tr.append(td); 
                td.css({
                    "width": panels.data.grid_width + 'px',
                    "height": panels.data.grid_height + 'px',
                });
                td.addClass('playmat-column');
                if ( rows_cols[row][col] !== 0 ) {
                    td.append(rows_cols[row][col]);
                } 
            }
            table.append(tr);
        }
        target.html('')
        target.append(table);
    }
    function render_players(players) {
        $.each(players, function () {
            let id = 'player_' + this.id;
            let d = _div( id, 'player tab');
            render_play_mat(this,d);
            panels.main.append(d);
            let btn = _tab_button(id,this);
            panels.tab_panel.append(btn);
            d.hide();
        });
    }
    $(function () {
        panels.main = $('div.main');
        panels.tab_panel = $('div.tab-panel');
        panels.tab_panel.css('height',$(window).height() * 0.05);
        panels.data.grid_width = get_grid_column_width();
        panels.data.grid_height = get_grid_column_height();
        $.each(window.board.players, function () {
            this.playmat = get_base_table();
        });
        render_players(window.board.players);
        $('#player_1_tab_button').trigger($.Event('click'));
    });
})(jQuery);
