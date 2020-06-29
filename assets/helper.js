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
