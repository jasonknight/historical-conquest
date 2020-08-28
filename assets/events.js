function trigger_attack(p1,p2,src_id,card1,card2) {
    let e = $.Event('attack');
    e.attacking_player = p1;
    e.defending_player = p2;
    e.attack_source = src_id;
    e.attacking_land = card1;
    e.defending_land = card2;
    $('body').trigger(e);
}
function trigger_close_dialog(sender,dialog) {
    let e = $.Event('dialog.close');
    e.dialog = dialog;
    e.sender = sender;
    $('body').trigger(e);
}
function trigger_refresh() {
    $('body').trigger($.Event('refresh_board'));
}
function trigger_close_zoom_holder() {
    $('body').trigger($.Event('close_zoom_holder'));
}
function trigger_card_controls_create(d,src,clone,button_row) {
    let e = $.Event('card_controls_create');
    e.d = d;
    e.src = src;
    e.clone = clone;
    e.button_row = button_row;
    $('body').trigger(e);
}
function trigger_activate_ability(d,src,clone,a) {
    let e = $.Event('ability.activate');
    e.d = d;
    e.src = src;
    e.clone = clone;
    e.ability = a;
    $('body').trigger(e);
}
function trigger_card_played(p,def,pdef) {
    let e = $.Event('card.played');
    e.player = p;
    e.card_def = def;
    e.played_def = pdef;
    $('body').trigger(e);
}
function trigger_draw(p) {
    let e = $.Event('card.draw');
    e.player = p;
    $('body').trigger(e);
}
function trigger_you_cant_do_that(msg) {
    _log('you cant do: ',msg);
}
// Event binding is here
$(function () {
    $('body').on('card_controls_create',function (e) {
        maybe_add_attack_controls(e.d,e.src,e.clone,e.button_row);
        maybe_add_explorer_controls(e.d,e.src,e.clone,e.button_row);
        maybe_add_abilities_button(e.d,e.src,e.clone,e.button_row);
        add_discard_button(e.d,e.src,e.clone,e.button_row);
    });
    $('body').on('card_zoom_show_abilities',function (e)    {
        _log('show_abilities',e);   
        convert_to_abilities_widget(e.d,e.src,e.clone);
    });
    $('body').on('ability.activate',function (e) {
        _log('Activate Ability',e.ability);
    });
    $('body').on('card.discard',function (e) {
        discard(get_current_player(),e.def);
    });
    $('body').on('card.draw',function (e) {
        draw(e.player);
    });
    $('body').on('card.played',function (e) {
        maybe_add_abilities(e.player,e.card_def,e.played_def);
    });
    $('body').on('card_zoom.show_attack_options',function (e) {
        _log('in body.on(card_zoom.show_attack_options)');
        show_attack_options(get_current_player(),e.d,e.src);
    });
    $('body').on('dialog.close', function (e) {
        $('div.xbtn').remove();
        e.dialog.remove();
        e.sender.remove();
    });
    $('body').on('attack',function (e) {
        show_attack_dialog(e.attacking_player,e.defending_player,e.attack_source,e.attacking_land,e.defending_land);
    });
});
