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
    <?php $asset('helper.js'); ?> 
    <?php $asset('events.js'); ?> 
    <?php $asset('card_zoom.js'); ?>
    <?php $asset('explorer.js'); ?>
    <?php $asset('land_card.js'); ?>
    <?php $asset('abilities.js'); ?>
    <?php $asset('attack.js'); ?>
    
    <?php echo get_type_conversion_js(); ?> 
    _log("Logging","is","on");
    
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
        _log("setup_hand",player.hand);
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
            width: w * 0.97,
        });
        bar.append(cont);
        cont = _div(null,'morale-display'); 
        cont.html(get_current_round());
        w = panels.main.width() / 10;
        cont.css({
            width: w * 0.97,
        });
        bar.append(cont);
        target.append(bar);


    }
    function render_damage_mat(player,target) {
        let rows_cols = player.damagemat;
        let rl = rows_cols.length;
        let cl = rows_cols[0].length;
        rows_cols[rl-2][cl-1] = 'LAND_PILE';
        rows_cols[rl-2][cl-2] = 'DRAW_PILE';
        rows_cols[rl-3][cl-1] = 'DISCARD_PILE';
        player.damagemat = rows_cols;
        target.html('')
        let table = $('<table></table>');
            table.addClass('grid');
        let last_row = rows_cols.length - 1;
        let last_col = rows_cols[0].length - 1;
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
                td.addClass('playmat-column damagemat-column');
                if ( rows_cols[row][col] !== 0 ) {
                    let id = rows_cols[row][col];
                    if ( id == 'LAND_PILE' ) {
                        td.addClass('land-pile');
                    } else if ( id == 'DRAW_PILE' ) {
                        td.addClass('draw-pile');
                    } else if ( id == 'DISCARD_PILE' ) {
                        td.addClass('discard-pile');
                    } else {
                        // Okay we handle abilities display here
                        let abs = rows_cols[row][col];
                        td.html('');
                        for ( let i = 0; i < abs.length; i++ ) {
                            let mhtml = dam_item_to_html(abs[i]);
                            td.append(mhtml);
                        }
                    }
                } 
            }
            table.append(tr);
        }
        target.append(table);
    }
    function render_ability_mat(player,target) {
        let rows_cols = player.abilitymat;
        let rl = rows_cols.length;
        let cl = rows_cols[0].length;
        rows_cols[rl-2][cl-1] = 'LAND_PILE';
        rows_cols[rl-2][cl-2] = 'DRAW_PILE';
        rows_cols[rl-3][cl-1] = 'DISCARD_PILE';
        player.abilitymat = rows_cols;
        target.html('')
        let table = $('<table></table>');
            table.addClass('grid');
        let last_row = rows_cols.length - 1;
        let last_col = rows_cols[0].length - 1;
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
                td.addClass('playmat-column abilitymat-column');
                if ( rows_cols[row][col] !== 0 ) {
                    let id = rows_cols[row][col];
                    if ( id == 'LAND_PILE' ) {
                        td.addClass('land-pile');
                    } else if ( id == 'DRAW_PILE' ) {
                        td.addClass('draw-pile');
                    } else if ( id == 'DISCARD_PILE' ) {
                        td.addClass('discard-pile');
                    } else {
                        // Okay we handle abilities display here
                        let abs = rows_cols[row][col];
                        td.html('');
                        for ( let i = 0; i < abs.length; i++ ) {
                            let mhtml = mat_item_to_html(abs[i]);
                            td.append(mhtml);
                        }
                    }
                } 
            }
            table.append(tr);
        }
        target.append(table);
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
                        td.on('click',function () {
                            _log('draw player',player);
                            trigger_draw(player);
                        });
                    } else if ( id == 'DISCARD_PILE' ) {
                        td.addClass('discard-pile');
                    } else if ( window.carddb[id] ) {
                       td.append(get_card(id)); 
                    } else {
                        _log("Don't know ",id);
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
    function draw(player) {
        if ( player.hand.length > 4 ) {
            trigger_you_cant_do_that('draw a card because ' + player.hand.length);
            _log(player.hand);
            return;
        }
        if ( player !== get_current_player() ) {
            trigger_you_cant_do_that("it's not your turn");
            return;
        }
        // making sure the reference is correct and
        // not a copy
        player = get_current_player();
        let run = true;
        let cap = 10;
        let i = 0;
        while ( run ) {
            i++;
            let c = player.draw_pile.pop();
            let def = get_card_def(c);
            if ( i > cap ) {
                // TODO: out of cards error?
                _log('error');
            }
            if ( def.maintype == window.types.key_values.CARD_LAND ) {
                player.land_pile.push(c);
                continue;
            }
            player.hand.push(c);
            run = false;
            trigger_refresh();
        }
    }
    function discard(player,card_def) {
        if ( player.hand.indexOf(card_def.ext_id) != -1 ) {
            let nh = [];
            for ( let i = 0; i < 5; i++ ) {
                 if ( player.hand[i] == card_def.ext_id ) {
                     _log('removing',card_def.ext_id);
                     continue;
                 }
                 nh.push(player.hand[i]);
            }
            player.hand = nh;
            set_current_player(player);
            advance_move();
            trigger_refresh();
            return;
        }
        let loc = get_row_col_of_played_card(player,card_def.ext_id);
        if ( loc && loc.row ) {
            player.playmat[loc.row][loc.col] = 0;
            // TODO: Is this always the case? Maybe not.
            player.abilitymat[loc.row][loc.col] = 0;
            advance_move();
            set_current_player(player);
            trigger_refresh();
            return;
        }
        _log("Discarding failed?",player,card_def);
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
                if ( is_explorer(card_def.maintype) ) {
                    if ( player.playmat[y+1][x] == 0 ) {
                        unadvance_move();
                        play_card(player,player.land_pile[0],y+1,x);
                    }
                }
                trigger_card_played(player,card_def,played_def);
            } else {
                new_hand.push(player.hand[i]);
            }
        }
        _log('new_hand',new_hand);
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
                trigger_card_played(player,card_def,played_def);
            } else {
                new_land_pile.push(player.land_pile[i]);
            }
        }
        player.land_pile = new_land_pile;
        expand_playmat(player);
        advance_move();
    }
    function render_players(players) {
        render_players_abilitymat(players);
        render_players_damagemat(players);
        panels.main.html('');
        panels.tab_panel.html('');
        _log("Rendering", "Round", window.board.round, "Move", current_move());
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
    function render_players_abilitymat(players) {
        panels.abilitymats.html('');
        $.each(players, function () {
            let id = 'player_' + this.id + '_abilitymat';
            let d = _div( id, 'abilitymat-tab');
            render_ability_mat(this,d);
            panels.abilitymats.append(d);
            d.hide();
        });
    }
    function render_players_damagemat(players) {
        panels.damagemats.html('');
        $.each(players, function () {
            let id = 'player_' + this.id + '_damagemat';
            let d = _div( id, 'damagemat-tab');
            render_damage_mat(this,d);
            panels.damagemats.append(d);
            d.hide();
        });
    }
    function process_player(player) {
        let draw_pile = [];
        let land_pile = [];
        for ( let i = 0; i < player.draw_pile.length; i++ ) {
            let id = player.draw_pile[i];
            let card_def = window.carddb[id];
            if ( ! card_def ) {
                _log("id",id, "does not exist in carddb?");
                continue;
            }
            if ( card_def && card_def.maintype == window.types.key_values.CARD_LAND ) {
                land_pile.push(id);
            } else {
                draw_pile.push(id);
            }
        }
        if ( player.draw_pile.length < 1 ) {
            player.draw_pile = draw_pile;
        }
        if ( player.land_pile.length < 1) {
            player.land_pile = land_pile;
        }
        if ( current_move() == 0 ) {
            while (player.hand.length < 5) {
                let c = player.draw_pile.pop();
                let def = get_card_def(c);
                if ( def.maintype == window.types.key_values.CARD_LAND ) {
                    player.land_pile.push(c);
                    continue;
                }
                _log('appending',c);
                player.hand.push(c);
            }
        }
        return player;
    }
    function debug_playmat(player) {
        _log("Playmat", player.playmat.map(function (r) { return r.join('|'); }).join("\n"));
    }
    $(function () {
        panels.main = $('div.main');
        panels.abilitymats = $('div.abilitymats');
        panels.damagemats = $('div.damagemats');
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
            _log(window.board.players);
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
