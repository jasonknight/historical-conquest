function show_error_message(msg) {
    let dialog = window.get_error_dialog(); 
    let target = dialog.find('.error-panel');
    if ( target.length === 0 ) {
        target = dialog.find('.dialog-body');
    }
    target.append(
        '<p class="error">' +
        msg +
        '</p>'
    );
}
