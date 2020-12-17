<?php
namespace HistoricalConquest;
?>
(function ($) {
    let panels = {};
        panels.data = {};
        panels.data.card_width = 0.90;
        panels.data.card_height = 0.85
    let display = $('#player_cp_right');
    let possible_decks = <?php echo json_encode(get_possible_decks()); ?>;
    panels.main = display;
    <?php echo get_type_conversion_js(); ?> 
    <?php $asset('helper.js'); ?>
    function get_create_deck_form() {
        let form = $($('div.create-deck-template').html());
        form.find('.save-button').on('click',function () {
            let data = {};
            data.action = 'create_deck';
            data.deck_name = form.find('.deck-name').val();
            $.post(window.ajaxurl,data,function (resp) {
                if ( resp.status == 'OK' ) {
                    show_deck_manager();
                } else {
                    alert(resp.msg);
                }
            });
        });
        return form;
    }
    function update_card_count(editor) {
        let left_side_count = editor.find('.left-column .card').length;
        let right_side_count = editor.find('.right-column .card').length;
        editor.find('.count-display').html( left_side_count + ' / ' + right_side_count);
    }
    function move_card(editor,card) {
        if ( card.parent().hasClass('left-column') ) {
            // we move from the left to the right
            let fcard = editor.find('.right-column .card:first');
            if ( fcard.length == 0 ) {
                editor.find('.right-column').append(card);
            } else {
                card.insertBefore(fcard)
            }
            update_card_count(editor);
            return;
        } 
        let fcard = editor.find('.left-column .card:first');
        if ( fcard.length == 0 ) {
            editor.find('.left-column').append(card);
        } else {
            card.insertBefore(fcard)
        }
        update_card_count(editor);
    }
    function save_deck(editor) {
        let data = {};
            data.action = 'save_deck';
            data.deck_id = editor.attr('deck-id');
            data.card_ids = [];
            editor.find('.right-column .card').map(function () { data.card_ids.push($(this).attr('card-id')); });
        console.log("Saving deck",data);
        $.post(window.ajaxurl,data,function (resp) {

        });
    }
    function show_deck_editor(id) {
        let data = {};
        data.action = 'get_deck_cards';
        data.deck_id = id;
        $.post(window.ajaxurl,data,function (resp) {
            if ( resp.status != 'OK' ) {
                console.log('get_deck_cards error',resp);
                return;
            }
            resp = resp.cards;
            clear_element(display,'show_deck_editor in $.post');
            let editor = $($('div.deck-editor-template').html());
            display.append(editor);
            let seen_ext_ids = [];
            if ( Array.isArray(resp) ) {
                clear_element(editor.find('.right-column'),'Array.isArray in show_deck_editor');
                editor.attr('deck-id',id);
                for ( let i = 0; i < resp.length; i++ ) {
                    let card = resp[i];
                    seen_ext_ids.push(card.ext_id);
                    let cdisp = get_card(card.ext_id);
                    cdisp.unbind('click');
                    cdisp.css({
                        "float": "left",
                        "margin-right": "5px",
                        "margin-bottom": "5px",
                        "min-width": "15%",
                    });
                    cdisp.on('click',function () {
                        move_card(editor,$(this));
                        save_deck(editor);
                    }); 
                    editor.find('.right-column').append(cdisp);
                }
            }
            let cdata = {};
            cdata.action = "get_player_cards";
            $.post(window.ajaxurl,cdata,function (resp) {
                if ( resp.status != 'OK' ) {
                    console.log('failed to get player cards',resp);
                }
                resp = resp.cards;
                clear_element(editor.find('.left-column'),'ajax get_player_cards');
                editor.attr('deck-id',id);
                for ( let i = 0; i < resp.length; i++ ) {
                    let card = resp[i];
                    if ( seen_ext_ids.indexOf(card.ext_id) != -1 ) {
                        continue;
                    }
                    let cdisp = get_card(card.ext_id);
                    cdisp.unbind('click');
                    cdisp.css({
                        "float": "left",
                        "margin-right": "5px",
                        "margin-bottom": "5px",
                        "min-width": "15%",
                    });
                    cdisp.on('click',function () {
                        move_card(editor,$(this));
                        save_deck(editor);
                    }); 
                    editor.find('.left-column').append(cdisp);
                    update_card_count(editor); 
                }
            });
        });
    }
    function get_deck_display() {
        let d = $($('div.deck-display-template').html());
        d.on('click', function () {
           show_deck_editor($(this).attr('deck-id')); 
        });
        return d;
    }
    function clear_element(d,msg) {
        console.log("clearing ",msg,d);
        d.html('');
    }
    function show_deck_manager() {
        let data = {};
        data.action = 'get_decks';
        $.post(window.ajaxurl,data,function (resp) {
            if ( resp.status != 'OK' ) {
                console.log("WTF on get_decks",resp);
                return;
            }
            let decks = resp.decks;
            if ( decks == 0 ) {
                // there are no decks, show deck creation
                clear_element(display,'show_deck_manager');
                display.append( get_create_deck_form() );
            } else {
                clear_element(display,'show_deck_manager 2');
                for ( let i = 0; i < decks.length; i++ ) {
                    let deck = decks[i];
                    if ( deck.card_count >= 50 ) {
                        let found = false;
                        for ( let j = 0; j < possible_decks.length; j++ ) {
                            if ( possible_decks[j].id = deck.id ) 
                                found = true;
                        }
                        if ( ! found ) {
                            possible_decks.push(deck);
                        }
                    }
                    let disp = get_deck_display();
                    disp.find('.deck-name').html(deck.name + '('+deck.card_count+')');
                    disp.attr('deck-id',deck.id);
                    display.append(disp);
                }
                let cdeck = get_deck_display();
                cdeck.find('.deck-name').html("New Deck");
                cdeck.unbind('click');
                cdeck.on('click',function () {
                    clear_element(display,'cdeck.on');
                    display.append( get_create_deck_form() );
                    console.log("Done adding form");
                });
                display.append(cdeck);
            }
        });
    }
    function show_challenge_player() {
        console.log("show_challenge_player");
        if ( possible_decks.length == 0 ) {
            alert("You must first create a deck with at least 50 cards");
            return;
        }
       let data = {}; 
           data.action = 'get_games';
        let challenges = $($('div.challenges-template').html());
            clear_element(display,'show_challenge_player');
            display.append(challenges);
        let challenge_tmp = $($('div.challenge-player-template').html());
        let create_challenge = challenge_tmp.clone();
        let deck_select = create_challenge.find('select[name="deck"]');
        deck_select.find('option').remove();
        for ( let i = 0; i < possible_decks.length; i++ ) {
            deck_select.append('<option value="'+possible_decks[i].id+'">'+possible_decks[i].name+'</option>');
        }
        create_challenge.find('input[type=submit]').on('click',function () {
            console.log("Challenge!");
            let pid = create_challenge.find('select[name="opponent"]').val();
            let deck_id = create_challenge.find('select[name="deck"]').val();
            let data = {};
                data.action = "create_challenge";
                data.opponent = pid;
                data.deck = deck_id;
            $.post(window.ajaxurl,data,function (resp) {
                console.log('create_challenge',resp);
                show_challenge_player();
            });
        });
        challenges.find('.left-column').append(create_challenge);
        $.post(window.ajaxurl,data,function (resp) {
            console.log(data,resp);
            if ( resp.others_games.length == 0 ) {
                challenges.find('.right-column').append('<p class="centered">You have no open challenges</p>');
            }
            let cont_fn = function (game) {
                let players = [];
                for ( let j = 0; j < game.players.length; j++ ) {
                    players.push(game.players[j].name);
                }
                players = players.join(' VS ');
                let cont = $('<div class="challenge" />');
                let stat = $('<div class="column status" />');
                if ( game.active == "0") {
                    stat.addClass('inactive');
                } else {
                    stat.addClass('active');
                }
                cont.append(stat);
                cont.append('<div class="column players">' + players + '</div>');
                if ( game.active == '0' ) {
                    cont.append('<div class="column play-btn">Waiting</div>');
                } else {
                    cont.append('<div class="column play-btn">Play</div>');
                }
                return cont;
            };
            for ( let i = 0; i < resp.my_games.length; i++ ) {
                let game = resp.my_games[i];
                let cont = cont_fn(game); 
                console.log("cont",cont);
                let btn = cont.find('.play-btn');
                btn.on('click',function () {
                        window.location = '/?action=historical-conquest-game&game_id=' + game.id;
                });
                challenges.find('.left-column').append(cont);
            }
            for ( let i = 0; i < resp.others_games.length; i++ ) {
                let game = resp.others_games[i];
                let cont = cont_fn(game); 
                // TODO: We need to modify the  display so that the
                // person can accept the challenge
                let deck_select = $('<select name="deck"></select>');
                for ( let j = 0; j < possible_decks.length; j++ ) {
                    deck_select.append('<option value="'+possible_decks[j].id+'">'+possible_decks[j].name+'</option>');
                }
                let btn = cont.find('.play-btn');
                if ( game.active == '0' ) {
                        btn.html('Accept');
                        btn.on('click',function (){
                           let data = {}; 
                               data.action = "accept_game";
                               data.game = game.id;
                               data.deck = deck_select.val();
                            $.post(window.ajaxurl,data,function (resp) {
                                console.log("accept_game",resp);
                                show_challenge_player();
                            });
                        });
                    let dbtn = btn.clone();
                    let col = $('<div class="column" />');
                        col.append(deck_select);
                    col.insertBefore(btn);
                    dbtn.html('Decline');
                    dbtn.addClass('decline-btn');
                    dbtn.insertAfter(btn);
                } else {
                    // TODO: When the game is active, we need
                    // to let the user go to the game to play it
                    btn.on('click',function () {
                        window.location = '/?action=historical-conquest-game&game_id=' + game.id;
                    });
                }
                challenges.find('.right-column').append(cont);
            }
        });
    }
    function setup() {
        $('a.manage-decks').on('click',function () {
            let e = $.Event('player_cp.manage_decks');
            $('body').trigger(e);
        });
        $('a.challenge-player').on('click',function () {
            let e = $.Event('player_cp.challenge_player');
            $('body').trigger(e);
        });
    }
    $(setup);
    $('body').on('player_cp.manage_decks',show_deck_manager);
    $('body').on('player_cp.challenge_player',show_challenge_player);
})(jQuery);
