(function ($) {
    let panels = {};
        panels.data = {};
        panels.data.card_width = 0.90;
        panels.data.card_height = 0.85
    let current_move = 0;
    function get_current_player() {
        return window.board.players[0];
    }
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
    function get_grid_column_width(player) {
        let grid = get_base_table();
        if ( player ) {
           grid = player.playmat; 
        }
        return (panels.main.width() / grid[0].length);
    }
    function get_card_column_width(player) {
        return get_grid_column_width(player) * panels.data.card_width;
    }
    function get_grid_column_height(player) {
        let grid = get_base_table();
        if ( player ) {
           grid = player.playmat; 
        }
        let h = $(window).height() * 0.85;
        return (h/grid.length);
    }
    function get_card_column_height(player) {
        return get_grid_column_height(player) * panels.data.card_height;
    }
    function get_land_pile() {
        let d = _div(null,null);
        d.addClass('land-pile');
        return d;
    }
    function can_play(card_element) {
        if ( current_move > 2 )
            return false;
        let td = card_element.parent();
        let p = get_current_player();
        let last_row = p.playmat.length - 1;
        if ( td.attr('y') == last_row ) {
            return true;
        }
        return false;
    }
    function unhighlight_playable_squares() { 
        $('table.grid td.highlight-square').unbind('click');
        $('table.grid td.highlight-square').removeClass('highlight-square');
    }
    function highlight_playable_squares_for(card) {
        let p = get_current_player();    
        let mat = p.playmat;
        let last_row = mat.length - 1;
        let last_col = mat[0].length - 1;
        // For regular cards
        let playable_squares = [];
        for ( let y = 0; y <= last_row - 2; y++ ) {
            for ( let x = 0; x <= last_col - 2; x++ ) {
               if ( mat[last_row-1][x] != 0  && mat[y][x] == 0) {
                    playable_squares.push([y,x]);
               }
            }
        }
        if ( card.hasClass('card-type-explorer') ) {
            let y = last_row - 2;
            for ( let x = 0; x <= last_col - 2; x++ ) {
               if ( mat[y+1][x] == 0 && mat[y+1][x+1] != 0 && mat[y][x] == 0 ) {
                    playable_squares.push([y,x]);
               }
            }
        }
        console.log('playable_squares',playable_squares);
        for ( let i = 0; i < playable_squares.length; i++ ) {
            let pos = playable_squares[i];
            $('table.grid td[yx="' + pos.join(',') + '"]').addClass('highlight-square');
        }
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
            unhighlight_playable_squares();
        });
        if ( can_play(src) ) {
            highlight_playable_squares_for(src);
        }
        $('table.grid td.highlight-square').on('click',function() {
            let id = src.attr('card-id');
            let p = get_current_player();
            let y = $(this).attr('y');
            let x = $(this).attr('x');
            play_card(p,id,y,x);
            $('body').trigger($.Event('refresh_board'));
            d.remove();
            unhighlight_playable_squares();
        });
        d.append(clone);
        return d;
    }
    function get_card(id) {
        let card = $($('div.card-template').html()) ;
        let card_def = window.carddb[id];
        if ( card_def ) {
            card.find('.name-plate').html(card_def.name);
            card.addClass('card-type-' + card_def.type);
            if ( card_def.subtype1 ) {
                card.addClass('card-type-' + card_def.subtype1);
            }
        }
        card.attr('card-id',id);
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
        table[row][col] = card;
    }
    function setup_hand(player,rows_cols) {
        let rl = player.playmat.length;
        let cl = player.playmat[0].length;
        let last_row =  rl - 1;
        let last_col = cl - 1;
        let row = last_row;
        let col = last_col;
        for ( let i = 0; i < 5; i++) {
            if ( !player.hand[i] ) {
                rows_cols[row][col] = 0;
                continue;
            }
            rows_cols[row][col] = player.hand[i];
            col--;
        }
    }
    function render_play_mat(player,target) {
        let rows_cols = player.playmat;
        let rl = rows_cols.length;
        let cl = rows_cols[0].length;
        rows_cols[rl-2][cl-1] = 'LAND_PILE';
        rows_cols[rl-2][cl-2] = 'DRAW_PILE';
        rows_cols[rl-3][cl-1] = 'DISCARD_PILE';
        player.playmat = rows_cols;
        let table = $('<table></table>');
            table.addClass('grid');
        let last_row = rows_cols.length - 1;
        let last_col = rows_cols[0].length - 1;
        let land_card_present = false;
        for ( let col = last_col - 2; col >= 0; col-- ) {
           if ( rows_cols[last_row-1][col] != 0 ) {
                land_card_present = true;
           }
        }
        if ( ! land_card_present ) {
            play_card(player,player.land_pile[0],last_row - 1,last_col - 2);
            current_move--;
            rows_cols = player.playmat;
        }
        setup_hand(player,rows_cols);
        for ( let row = 0; row < rows_cols.length; row++) {
            let tr = $('<tr />');
            tr.attr('y',row);
            for ( let col = 0; col < rows_cols[0].length; col++ ) {
                let td = $('<td />');
                tr.append(td); 
                td.attr('y',row);
                td.attr('x',col);
                td.attr('yx',row + ',' + col);
                td.css({
                    "width": get_grid_column_width(player) + 'px',
                    "height": get_grid_column_height(player) + 'px',
                });
                td.addClass('playmat-column');
                if ( rows_cols[row][col] !== 0 ) {
                    let id = rows_cols[row][col];
                    if ( id == 'LAND_PILE' ) {
                        td.addClass('land-pile');
                    } else if ( id == 'DRAW_PILE' ) {
                        td.addClass('draw-pile');
                    } else if ( id == 'DISCARD_PILE' ) {
                        td.addClass('discard-pile');
                    } else if ( window.carddb[id] ) {
                       td.append(get_card(id)); 
                    } else {
                        console.log("Don't know ",id);
                    }
                } 
            }
            table.append(tr);
        }
        target.html('')
        target.append(table);
    }
    function get_card_def(id) {
        return window.carddb[id];
    }
    function play_card(player,id,y,x) {
        y = parseInt(y);
        x = parseInt(x);
        if ( current_move > 2 )
            return;
        let card = get_card(id);
        let card_def = get_card_def(id);
        let new_hand = [];
        
        for ( let i = 0; i < player.hand.length; i++ ) {
            if ( card && player.hand[i] == id ) {
                let played_def = {};
                played_def.id = id;
                played_def.y = y;
                played_def.x = x;
                player.played.push(played_def);
                player.playmat[y][x] = id;
                console.log("card_def",card_def);
                if ( card_def.subtype1 == 'explorer' ) {
                    if ( player.playmat[y+1][x] == 0 ) {
                        play_card(player,player.land_pile[0],y+1,x);
                        current_move--;
                    }
                }
            } else {
                new_hand.push(player.hand[i]);
            }
        }
        player.hand = new_hand;
        let new_land_pile = [];
        for ( let i = 0; i < player.land_pile.length; i++ ) {
            if ( card && player.land_pile[i] == id ) {
                let played_def = {};
                played_def.id = id;
                played_def.y = y;
                played_def.x = x;
                player.played.push(played_def);
                player.playmat[y][x] = id;
            } else {
                new_land_pile.push(player.land_pile[i]);
            }
        }
        player.land_pile = new_land_pile;
        if ( y == 0 ) {
            let nr = [];
            for ( let i = 0; i < player.playmat[0].length; i++ ) {
                nr[i] = 0;
            }
            let new_mat = [nr];
            for ( let i = 0; i < player.playmat.length; i++) {
                new_mat.push(player.playmat[i]);
            }
            player.playmat = new_mat;
        }
        current_move++;
    }
    function render_players(players) {
        panels.main.html('');
        panels.tab_panel.html('');
        console.log("Rendering", "Round", window.board.round, "Move", current_move);
        $.each(players, function () {
            let id = 'player_' + this.id;
            let d = _div( id, 'player tab');
            render_play_mat(this,d);
            panels.main.append(d);
            let btn = _tab_button(id,this);
            panels.tab_panel.append(btn);
            d.hide();
        });
        $('#player_1_tab_button').trigger($.Event('click'));
    }
    function process_player(player) {
        let draw_pile = [];
        let land_pile = [];
        for ( let i = 0; i < player.draw_pile.length; i++ ) {
            let id = player.draw_pile[i];
            let card_def = window.carddb[id];
            if ( card_def.type == 'land' ) {
                land_pile.push(id);
            } else {
                draw_pile.push(id);
            }
        }
        player.draw_pile = draw_pile;
        player.land_pile = land_pile;
        if ( window.board.round == 0 && current_move == 0 ) {
            while (player.hand.length < 5) {
                player.hand.push(player.draw_pile.pop());
            }
        }
        console.log("hand",player.hand);
        return player;
    }
    function debug_playmat(player) {
        console.log("Playmat", player.playmat.map(function (r) { return r.join('|'); }).join("\n"));
    }
    $(function () {
        panels.main = $('div.main');
        panels.tab_panel = $('div.tab-panel');
        panels.tab_panel.css('height',$(window).height() * 0.05);
        $.each(window.board.players, function () {
            this.playmat = get_base_table();
            process_player(this);
        });
        $('body').on('refresh_board',function () {
            panels.data.grid_width = get_grid_column_width();
            panels.data.grid_height = get_grid_column_height();
            console.log(window.board.players);
            render_players(window.board.players);
        });
        $('body').trigger($.Event('refresh_board'));
        $('#player_1_tab_button').trigger($.Event('click'));
    });
})(jQuery);
