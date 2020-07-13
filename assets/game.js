<?php
namespace HistoricalConquest;
?>
(function ($) {
    let panels = {};
        panels.data = {};
        panels.data.card_width = 0.90;
        panels.data.card_height = 0.85
    window.board.current_move = 0;
    window.board.player_pointer = 0;
    <?php $asset('card_zoom.js'); ?>
    <?php $asset('explorer.js'); ?>
    <?php $asset('land_card.js'); ?>
    
    <?php $asset('helper.js'); ?> 
    <?php echo get_type_conversion_js(); ?> 
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
        for ( let i = 0; i < playable_squares.length; i++ ) {
            let pos = playable_squares[i];
            $('table.grid td[yx="' + pos.join(',') + '"]').addClass('highlight-square');
        }
    }

   
    
    function contains_card_type(lst,t,s1) {
        for ( let i = 0; i < lst.length; i++ ) {
            if ( t && s1 ) {
                if ( window.carddb[lst[i]] && window.carddb[lst[i]].maintype == t) {
                    return true;
                }
            } else if (t) {
                if ( window.carddb[lst[i]] && window.carddb[lst[i]].maintype == t ) {
                    return true;
                }
            } else if (s1) {
                if ( window.carddb[lst[i]] && window.carddb[lst[i]].subtype1 == s1 ) {
                    return true;
                }
            }
        }
        return false;
    }
    function get_card(id) {
        let card = $($('div.card-template').html()) ;
        let card_def = window.carddb[id];
        if ( card_def ) {
            card.find('.name-plate').html(card_def.name);
            card.addClass('card-type-' + type_to_css_class(card_def.maintype));
            if ( type_to_css_class(card_def.maintype).match(/explorer-/) ) {
                card.addClass('card-type-explorer');
            }
            if ( is_character(card_def.maintype) ) {
                card.addClass('card-type-character');
            }
        }
        card.attr('card-id',id);
        card.css({
            "width": get_card_column_width() + 'px',
            "height": get_card_column_height() + 'px',
        });
        card.on('click',function () {
            $('div.card-zoom-holder').remove();
            $('.card-controls').remove();
            unhighlight_playable_squares();
            let cont = get_card_zoom_holder($(this),true);
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
    function render_morale(player,target) {
        let s = 100;
        let end = 3000;
        let step = 100;
        let c = s;
        let last_c = c;
        let bar = _div('morale_bar_' + player.id,'morale-bar');
        while ( c < end ) {
           let cont = _div(null,'morale-display'); 
            cont.html(c);
            let w = panels.main.width() / 10 * 0.98;
            cont.css({
                width: w,
            });
            bar.append(cont);
            if ( get_player_morale(player) >= c ) {
                cont.addClass('morale-display-current');
            }
            last_c = c;
            c = c + step;
            if ( step < 500 ) {
                step = step + 100;
            }
            
        }
        cont = _div(null,'morale-display'); 
        cont.html(end);
        w = panels.main.width() / 10;
        cont.css({
            width: w,
        });
        bar.append(cont);
        cont = _div(null,'morale-display'); 
        cont.html(get_current_round());
        w = panels.main.width() / 10;
        cont.css({
            width: w,
        });
        bar.append(cont);
        target.append(bar);


    }
    function render_play_mat(player,target) {
        let rows_cols = player.playmat;
        let rl = rows_cols.length;
        let cl = rows_cols[0].length;
        rows_cols[rl-2][cl-1] = 'LAND_PILE';
        rows_cols[rl-2][cl-2] = 'DRAW_PILE';
        rows_cols[rl-3][cl-1] = 'DISCARD_PILE';
        player.playmat = rows_cols;
        target.html('')
        render_morale(player,target);
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
            unadvance_move();
            play_card(player,player.land_pile[0],last_row - 1,last_col - 2);
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
        target.append(table);
    }
    function get_card_def(id) {
        return window.carddb[id];
    }
    function play_card(player,id,y,x) {
        y = parseInt(y);
        x = parseInt(x);
        if ( current_move() > 2 )
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
                console.log("card_def",card_def, is_explorer(card_def.maintype));

                if ( is_explorer(card_def.maintype) ) {
                    if ( player.playmat[y+1][x] == 0 ) {
                        unadvance_move();
                        play_card(player,player.land_pile[0],y+1,x);
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
        expand_playmat(player);
        advance_move();
    }
    function render_players(players) {
        panels.main.html('');
        panels.tab_panel.html('');
        console.log("Rendering", "Round", window.board.round, "Move", current_move());
        $.each(players, function () {
            let id = 'player_' + this.id;
            let d = _div( id, 'player tab');
            render_play_mat(this,d);
            panels.main.append(d);
            let btn = _tab_button(id,this);
            panels.tab_panel.append(btn);
            d.hide();
        });

        $(get_current_player_tab_button_id()).trigger($.Event('click'));
    }
    function process_player(player) {
        let draw_pile = [];
        let land_pile = [];
        for ( let i = 0; i < player.draw_pile.length; i++ ) {
            let id = player.draw_pile[i];
            let card_def = window.carddb[id];
            if ( ! card_def ) {
                console.log("id",id, "does not exist in carddb?");
                continue;
            }
            if ( card_def && card_def.maintype == window.types.key_values.CARD_LAND ) {
                land_pile.push(id);
            } else {
                draw_pile.push(id);
            }
        }
        player.draw_pile = draw_pile;
        player.land_pile = land_pile;
        if ( window.board.round == 0 && current_move() == 0 ) {
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
        if ( ! window.current_player ) {
            window.current_player = window.board.players[0];
        }
        $.each(window.board.players, function () {
            if ( this.playmat.length == 0 ) {
                this.playmat = get_base_table();
            }
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
        $('body').on('close_zoom_holder',function () {
            $('.card-zoom-holder').remove();
            $('.zoom-holder-child').remove();
            unhighlight_playable_squares();
        });
    });
})(jQuery);
