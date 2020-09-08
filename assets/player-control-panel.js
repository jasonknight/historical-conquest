<?php
namespace HistoricalConquest;
?>
(function ($) {
    let panels = {};
        panels.data = {};
        panels.data.card_width = 0.90;
        panels.data.card_height = 0.85
    let display = $('#player_cp_right');
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
    function show_deck_editor(id) {
        let data = {};
        data.action = 'get_deck_cards';
        data.deck_id = id;
        $.post(window.ajaxurl,data,function (resp) {
            console.log(data,resp);
            display.html(''); 
            let editor = $($('div.deck-editor-template').html());
            display.append(editor);
            let cdata = {};
            cdata.action = "get_player_cards";
            $.post(window.ajaxurl,cdata,function (resp) {
                if ( !Array.isArray(resp) ) {
                    alert("Couldn't fetch your owned cards");
                    return;
                }
                editor.find('.left-column').html('');
                for ( let i = 0; i < resp.length; i++ ) {
                    let card = resp[i];
                    let cdisp = get_card(card.ext_id);
                    cdisp.unbind('click');
                    cdisp.css({
                        "float": "left",
                        "margin-right": "5px",
                        "margin-bottom": "5px",
                        "min-width": "15%",
                    });

                    editor.find('.left-column').append(cdisp);
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
    function show_deck_manager() {
        let data = {};
        data.action = 'get_decks';
        $.post(window.ajaxurl,data,function (resp) {
            if ( resp.length == 0 ) {
                // there are no decks, show deck creation
                display.html('');
                display.append( get_create_deck_form() );
            } else {
                display.html('');
                for ( let i = 0; i < resp.length; i++ ) {
                    let deck = resp[i];
                    let disp = get_deck_display();
                    disp.find('.deck-name').html(deck.name);
                    disp.attr('deck-id',deck.id);
                    display.append(disp);
                }
                let cdeck = get_deck_display();
                cdeck.find('.deck-name').html("New Deck");
                cdeck.on('click',function () {
                    display.html('');
                    display.append( get_create_deck_form() );
                });
                display.append(cdeck);
            }
        });
    }
    function setup() {
        $('a.manage-decks').on('click',function () {
            let e = $.Event('player_cp.manage_decks');
            $('body').trigger(e);
        });
    }
    $(setup);
    $('body').on('player_cp.manage_decks',show_deck_manager);
})(jQuery);
