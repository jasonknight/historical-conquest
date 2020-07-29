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
    let e = $.Event('activate_ability');
    e.d = d;
    e.src = src;
    e.clone = clone;
    e.ability = a;
    $('body').trigger(e);
}
// Event binding is here
$(function () {
    $('body').on('card_controls_create',function (e) {
        maybe_add_explorer_controls(e.d,e.src,e.clone,e.button_row);
        maybe_add_abilities_button(e.d,e.src,e.clone,e.button_row);
    });
    $('body').on('card_zoom_show_abilities',function (e)    {
        _log('show_abilities',e);   
        convert_to_abilities_widget(e.d,e.src,e.clone);
    });
    $('body').on('activate_ability',function (e) {
        _log('Activate Ability',e.ability);
    });
});
