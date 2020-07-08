<?php
/**
 * Plugin Name: Historical Conquest Game
 * Plugin URI: https://lycanthropenoir.com
 * Description: Custom Plugin for the Historical Conquest Game
 * Version: 1.0
 * Author: Jason Martion <contact@lycanthropenoir.com>
 * Author URI: https://app.codeable.io/tasks/new?preferredContractor=43500&ref=76T6q
 * License: Private
 */
namespace HistoricalConquest;
require_once(__DIR__ . '/core.php');
require_once(__DIR__ . '/settings.php');
require_once(__DIR__ . '/types.php');
function install_game_tables() {
    global $wpdb;
    $sql = render_template('installation.sql');
    $queries = explode(';',$sql);
    foreach ($queries as $q) {
        if ( empty(trim($q)) ) {
            continue;
        }
        $wpdb->query($q);
        echo $q . '<br />';
        if ( $wpdb->last_error) {
            echo "ERROR" . $wpdb->last_error . '<br />';
        }
    }
}
function init() {
   add_action('template_redirect',__NAMESPACE__ . '\maybe_play_game'); 
   initialize_notices();
   if ( get('install-game-tables') ) {
       install_game_tables();
       exit; 
   }
   if ( get('import-cards-table') ) {
       import_cards_table();
       exit; 
   }
   if ( get('subaction') == 'edit-card' ) {
        edit_card();
   }
}
function initialize_notices() {
    update_option('hcnotices',[]);
}
function edit_card() {
    global $wpdb;
    $card = post('card');
    $abilities = post('abilities');
    if ( $card['id'] == '0' ) {
        create_new_card($card,$abilities);
        return;
    } 
    $updates = [];
    $card['updated_at'] = date('Y-m-d H:i:s');
    foreach ( $card as $key=>$value ) {
        if ( $key == 'id' )
            continue;
        if ( in_array($key,['ethnicity','maintype','continent','religion','climate']) ) {
            if ( defined($value) ) {
                $value = constant($value);
            }
        }
        $updates[] = $wpdb->prepare("`$key` = %s",$value);
    }
    $sql = "UPDATE `hc_cards` SET " . join(',',$updates) . " WHERE `id` = {$card['id']}";
    $wpdb->query($sql); 
    if ( $wpdb->last_error) {
        add_notice($wpdb->last_error);
    }
    if ( !is_array($abilities) ) {
        $abilities = [];
    }
    $wpdb->query( $wpdb->prepare("DELETE FROM `hc_card_abilities` WHERE card_id = %d",$card['id']) );
    foreach ( $abilities as $ability ) {
        create_new_ability($card,$ability);
    }
}
function create_new_card($card,$abilities) {

}
function create_new_ability($card,$ability) {
    global $wpdb;
    if ( empty($ability['description']) )
        return;
    $keys = [];
    $values = [];
    $ability['created_at'] = date('Y-m-d H:i:s');
    foreach ( ['apply_to_type','apply_to_scope'] as $def ) {
        if ( defined($ability[$def]) ) {
            $ability[$def] = constant($ability[$def]);
        } else {
            print_r($ability);
            die("$def {$ability[$def]} is not defined?");
        }
    }   
    $ability['card_id'] = $card['id'];
    foreach ( $ability as $key=>$value ) {
        if ( $key == 'id' )
            continue;
        $keys[] = "`$key`";
        if ( $key == 'card_id' ) {
            $values[] = $wpdb->prepare('%s',$card['id']);
        } else {
            $values[] = $wpdb->prepare('%s',$value);
        }
    }
    $sql = "INSERT INTO `hc_card_abilities` (" . join(',',$keys) . ") VALUES (" . join(',',$values) . ")";
    $wpdb->query($sql);
    if ( $wpdb->last_error) {
       add_notice($wpdb->last_error); 
    } 
}
function import_cards_table() {
    global $wpdb;
    include __DIR__ . '/tools/convert_html.php';
    $keys = array_keys($insertable_entries[0]);
    foreach ( $keys as &$key ) {
        $key = "`$key`";
    }
    $final_values = [];
    $lc = 0;
    foreach ( $insertable_entries as $i ) {
        if ( defined($i['maintype']) ) {
            $i['maintype'] = constant($i['maintype']);
        }
        if ( defined($i['continent']) ) {
            $i['continent'] = constant($i['continent']);
        }
        $values = array_values($i);
        foreach ( $values as &$value ) {
            if ( is_numeric($value) ) {
                $value = $wpdb->prepare('%d',$value);
            } else {
                $value = $wpdb->prepare('%s',$value);
            }
        }
        if ( count($values) != count($keys) ) {
            print_r($keys);
            print_r($i);
            echo "Count mismatch" . PHP_EOL;
            exit;
        }
        $final_values[] = '(' . join(',',$values) . ')'; 
    }
    $sql = "TRUNCATE `hc_cards`;";
    $wpdb->query($sql);
    $sql = "INSERT INTO `hc_cards` (" . join(',',$keys) . ') VALUES ' . join(',',$final_values) . ";";
    $wpdb->query($sql);
    echo $wpdb->last_error;
}
function maybe_play_game() {
    if ( get('action') == 'historical-conquest-game' ) {
        if ( get('admin-editor') ) {
            echo render_template('admin_editor.php');
        } else {
            echo render_template('game.php');
        }
        exit;
    }
}
require_once(__DIR__ . '/admin.php');
\add_action('init', __NAMESPACE__ . '\init');

