function _log() {
    console.log.apply(null,arguments);
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
        $('div.abilitymat-tab').hide();
        $('#' + id).show();
        $('#' + id + '_abilitymat').show();
        window.current_player = player;
        $(this).addClass('active-tab');
    });
    d.html(player.name);
    return d;
}
function get_base_table() {
    return [
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
    ];
}
function get_card(id,big) {
        let card = null;
        if ( typeof big != 'undefined' && big == true) {
            card = $($('div.card-template').html()) ;
        } else {
            card = $($('div.card-small-template').html()) ;
        }
        let card_def = window.carddb[id];
        if ( card_def ) {
            let def = $('<p />');
            def.html(card_def.name);
            
            card.find('.name-plate').html(def);
            card.addClass('card-type-' + type_to_css_class(card_def.maintype));
            if ( type_to_css_class(card_def.maintype).match(/explorer-/) ) {
                card.addClass('card-type-explorer');
            }
            if ( is_character(card_def.maintype) ) {
                card.addClass('card-type-character');
            }
            if ( card_def.illustration != '0' ) {
                let img = $('<img />');
                img.attr('src',card_def.illustration);
                let _h = ($('td.playmat-column:first').height() * 0.60);
                if ( big ) {
                    _h = ($('td.playmat-column:first').width() * 0.80)
                }
                let checker = function () {
                    if ( !img || ! img.length > 0 )
                        return;
                    if ( img.height() > card.height() ) {
                        img.css({ "height": (img.height() * 0.90) + 'px'});
                        setTimeout(checker,45);
                    }
                };
                setTimeout(checker,45);
                img.css({
                    "height": (_h) + 'px',
                    "margin-left": "auto",
                    "margin-right": "auto"
                });
                card.find('.illustration').append(img);
            }
            if ( card_def.background_image != '0' ) {
                //card.css({
                //    "background-image": 'url('+card_def.background_image+')',
                //});
            }
        }
        card.attr('card-id',id);
        card.css({
            "width": (get_card_column_width() * 0.50) + 'px',
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
       _log("Failed to find card " + id); 
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
    // i.e. this is in the players "hand"
    if ( td.attr('y') == last_row ) {
        return true;
    }
    return false;
}
function can_play_to(player,card_id,row,col,notices) {
    let def = get_card_def(card_id);
    let mat = player.playmat;
    if ( mat[row][col] != 0 ) {
        return false;
    }
    let tname = type_to_name(def.maintype);
    if ( tname.match(/CHARACTER/) || tname.match(/ARMY/) ) {
        // Each land has a carry_capacity
        let cap = get_carray_capacity_of_col(player,col);
        if ( cap <= 0 ) {
            notices.push("That land is at capacity for character and army cards.");
            return false;
        }
    }
}
function notice(msg) {
    window.notices.push(msg);
}
function add_notices(notices) {
    for ( let i = 0; i < notices.length; i++ ) {
        window.notices.push(notices[i]);
    }
}
function get_carray_capacity_of_col(player,col) {
    // TODO: Need to calculate carry capacity, and
    // maybe take into account abilities
    let mat = player.mat;
    let total = 4;
    for ( let row = 0; row < mat.length - 2; row++) {
        let cid = mat[row][col];
        let def = get_card_def(cid);
        let tname = type_to_name(def.maintype);
        if ( tname.match(/CHARACTER/) || tname.match(/ARMY/) ) {
            total--;
        }
    }
    return total;
}

function next_player() {
    window.board.player_pointer++;
    if ( window.board.player_pointer >= window.board.players.length ) {
        window.board.player_pointer = 0;
        next_round();
    }
    window.board.current_move = 0;
    process_player(window.board.players[window.board.player_pointer]);
}
function next_round() {
    window.board.round++;
}
function advance_move() {
    window.board.current_move++;
    if ( window.board.current_move == 3 ) {
        next_player();
    }
}
function unadvance_move() {
    window.board.current_move--;
}
function current_move() {
    return window.board.current_move;
}
function get_current_player() {
    return window.board.players[window.board.player_pointer];
}
function set_current_player(p) {
   window.board.players[window.board.player_pointer] = p; 
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
    _log("Type",type,m);
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

function get_player_morale(player) {
    let mat = player.abilitymat;
    let morale = 800;
    for ( let row = 0; row < mat.length; row++ ) {
        for ( let col = 0; col < mat[row].length; col++) {
            let abs = mat[row][col];
            if ( Array.isArray(abs) ) {
                for ( let i = 0; i < abs.length; i++ ) {
                    if ( abs[i].charges == 0 ) {
                        continue;
                    }
                    let a = mat_item_to_ability(abs[i]);
                    if ( a.apply_to_type != window.types.APPLY_PLAYER) {
                        continue;
                    }
                    if ( a.affects_attribute !== 'morale') {
                        continue;
                    }
                    _log("Adding moral from ability",a);
                    morale = morale + parseInt(a.affect_amount);
                }
            }
        }
    }
    return morale;
}
function get_land_attr(player,card_id,attr) {
    let loc = get_row_col_of_played_card(player,card_id);    
    let mat = player.playmat;
    for ( let row = 0; row < mat.length; row++) {
        
    }
}
function get_current_round() {
    return integer_to_roman(window.board.round + 1);
}
function integer_to_roman(num) {
    if (typeof num !== 'number') 
        return false; 

    var digits = String(+num).split(""),
    key = ["","C","CC","CCC","CD","D","DC","DCC","DCCC","CM",
    "","X","XX","XXX","XL","L","LX","LXX","LXXX","XC",
    "","I","II","III","IV","V","VI","VII","VIII","IX"],
    roman_num = "",
    i = 3;
    while (i--) {
        roman_num = (key[+digits.pop() + (i * 10)] || "") + roman_num;
    }
    return Array(+digits.join("") + 1).join("M") + roman_num;
}
function get_current_player_tab_button_id() {
    return '#player_' + get_current_player().id + '_tab_button';
}
function get_row_col_of_played_card(player,id) {
    let mat = player.playmat;
    for ( let row = 0; row < mat.length; row++) {
        for ( let col = 0; col < mat[row].length; col++ ) {
            if ( mat[row][col] === id ) {
                return {
                    row: row,
                    col: col,
                };
            }
        }
    }
    return {};
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

function create_dialog(id) {
    let d = $('<div class="dialog" id=' + id + '></div>');
    $('body').append(d);
    let body = {};
    body.width = $(window).width();
    body.height = $(window).height();
    _log("body",body);
    d.css({
        "width": (body.width * 0.80) + 'px',
        "height": (body.height * 0.80) + 'px',
        "left": (body.width * 0.10) + 'px',
        "top": (body.height * 0.10) + 'px',
        "position": "absolute",
        "z-index": window.layers.popup,
    });
    d.show();
    trigger_close_zoom_holder();
    return d;
}
