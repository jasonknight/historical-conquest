<?php
namespace HistoricalConquest\Admin;
require_once(__DIR__ . '/core.php');
require_once(__DIR__ . '/settings.php');
/*
	We only want the plugin to run on certain pages.
*/
function should_run() {
    global $pagenow;
    return true;
}

function init() {
   if ( !should_run() ) 
       return;
}
\add_action('admin_init', __NAMESPACE__ . '\init');

