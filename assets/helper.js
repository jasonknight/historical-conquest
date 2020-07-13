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
        window.current_player = player;
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
function get_card_def(id) {
    let def = window.carddb[id];
    if ( ! def ) {
       console.log("Failed to find card " + id); 
    }
    return def;
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
function get_current_player() {
    return window.current_player;
}

function get_card_summary(id) {
    let def = window.carddb[id];
    if ( def ) {
        return $('<p />').html(def.summary);
    }
    return '';
}
function get_card_abilities(id) {
    let def = window.carddb[id];
    if ( def ) {
        return $('<p />').html(def.abilities);
    }
    return '';
}
function get_card_abilities(id) {
    let def = window.carddb[id];
    if ( def ) {
        return $('<p />').html(def.abilities);
    }
    return '';
}
function get_card_year(id) {
    let def = window.carddb[id];
    if ( def ) {
        return $('<p />').html(def.year);
    }
    return '';
}
function get_card_attack_defense(id) {
    let def = window.carddb[id];
    if ( def ) {
        return $('<p />').html(def.attack_strength + '/' + def.defense_strength);
    }
    return '';
}
function is_land_row_card(el) {
   let td = el.parent();
    if ( parseInt(td.attr('y')) == get_current_player().playmat.length - 2 ) 
        return true;
    return false;
}
function is_character(t) {
    return type_to_name(t).match(/CHARACTER/);
}
function is_explorer(t) {
    let type = type_to_name(t);
    let m = type.match(/EXPLORER/)
    console.log("Type",type,m);
    return m;
}
function is_army(t) {
    return t == window.types.key_values.CARD_ARMY;
}
function get_row_col_for(player,id) {
    let mat = player.playmat;
    for ( let row = 0; row < mat.length - 1; row++ ) {
        for (let col = 0; col < mat[0].length - 2; col++ ) {
            if ( mat[row][col] == id ) {
                return {
                    row: row,
                    col: col
                };
            }
        }
    }
    return null;
}
function expand_playmat(player) {
    let vexpand = false;
    let hexpand = false;
    for ( let y = 0; y < player.playmat.length - 1; y++ ) {
        if ( player.playmat[y][0] != 0 ) {
            hexpand = true;
        }
    }
    if ( hexpand ) {
        for ( let y = 0; y < player.playmat.length; y++ ) {
            let nr = [0];
            let len = player.playmat[0].length;
            for ( let x = 0; x < len; x++ ) {
                nr.push(player.playmat[y][x]);
            }
            player.playmat[y] = nr;
        }
    }
    for ( let x = 0; x < player.playmat[0].length; x++ ) {
        if ( player.playmat[0][x] != 0 ) {
            vexpand = true;   
        }
    }
    if ( vexpand ) {
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
}
function get_next_open_row(player,row,col) {
    expand_playmat(player);
    let mat = player.playmat;
    if ( mat[row][col] == 0 ) {
        return row;
    }
    for ( let y = row; y > -1; y-- ) {
        if ( mat[y][col] == 0 ) {
            return y;
        }   
    }
    return null;
}
function trigger_refresh() {
    $('body').trigger($.Event('refresh_board'));
}
function trigger_close_zoom_holder() {
    $('body').trigger($.Event('close_zoom_holder'));
}
