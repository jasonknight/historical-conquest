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
   
   if ( get('admin-editor')) {
       session_start();
       if ( get('deck') ) {
           $_SESSION['deck_filter'] = get('deck');
       }
       if ( get('subaction') == 'edit-card' && is_array(post('card')) ) {
            edit_card();
       }
   }
   add_shortcode('hcgame_card_ability_listing',__NAMESPACE__ . '\shortcode_hcgame_card_ability_listing');
   add_shortcode('hcgame_admin',__NAMESPACE__ . '\shortcode_hcgame_admin');
   add_shortcode('hcgame_admin_card_images',__NAMESPACE__ . '\shortcode_hcgame_admin_card_images');
}
function shortcode_hcgame_card_ability_listing($attrs) {
    $cards = get_cards_without_abilities();
    echo render_template('card-ability-listing.php', ['cards' => $cards ]);
}
function upload_attachment($file,$file_key) {
    //dbg_notice(__FUNCTION__ . ": file_key=$file_key");
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    $user_id = \get_current_user_id();
    $errors = $file['error'];
    $upload_dir = \wp_upload_dir();
    $final_image_url = '';
    if ( ! is_array($errors) )
        $errors = [$errors];
    foreach (  $errors as $key => $error ) {
        if ( $error == UPLOAD_ERR_OK ) {
            $ufile = \wp_handle_upload($file,['test_form' => false]);
            $type = $file['type'];
            $name = $file['name'];
            $name_parts = pathinfo($name);
            $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
            $url = $ufile['url'];
            $file = $ufile['file'];
            $title = $name;
            // Build Attachment
            $attachment = array(
              'guid'           => $url,
              'post_mime_type' => $type,
              'post_title'     => $title,
              'post_content'   => "",
              'post_author' => \get_current_user_id()
            );
            // This should never be set as it would then overwrite an existing attachment
            if(isset($attachment['ID'])) {
              unset($attachment['ID']);
            }
            // Save the attachment metadata
            $attachment_id = \wp_insert_attachment($attachment, $file);

            // Update user avatar
            if(!is_wp_error($attachment_id)) {
                \wp_generate_attachment_metadata($attachment_id,$file);
                return $attachment_id; 
            }
        }
    }
    return null;
}
function shortcode_hcgame_admin_card_images($attrs) {
    global $wpdb;
    if ( files('illustration') ) {
        $aid = upload_attachment(files('illustration'),'none');
        if ( $aid ) {
            \update_post_meta($aid,'_card_ext',post('card_ext'));
            \update_post_meta($aid,'_card_id',post('card_id'));
            $sql = $wpdb->prepare("UPDATE `hc_cards` SET illustration = %d WHERE id = %d",$aid,post('card_id')); 
            $wpdb->query($sql);
        }
    }
    if ( files('background') ) {
        $aid = upload_attachment(files('background'),'none');
        if ( $aid ) {
            \update_post_meta($aid,'_card_ext',post('card_ext'));
            \update_post_meta($aid,'_card_id',post('card_id'));
            $sql = $wpdb->prepare("UPDATE `hc_cards` SET background_image = %d WHERE id = %d",$aid,post('card_id')); 
            $wpdb->query($sql);
        }
    }
    echo render_template('admin-editor/card-images.php');
}

function shortcode_hcgame_admin($attrs) {
    session_start();
    if ( get('deck') ) {
       $_SESSION['deck_filter'] = get('deck');
    }
    if ( get('subaction') == 'edit-card' && is_array(post('card')) ) {
        edit_card();
    }
    echo render_template('admin_editor.php');
}
function initialize_notices() {
    update_option('hcnotices',[]);
}
function splat_and_die($ar) {
    echo "<pre>".print_r($ar,true)."</pre>". PHP_EOL;
    die(__FUNCTION__);
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
        $updates[] = $wpdb->prepare("`$key` = %s",stripslashes($value));
    }
    $sql = "UPDATE `hc_cards` SET " . join(',',$updates) . " WHERE `id` = {$card['id']}";
    $wpdb->query($sql); 
    if ( $wpdb->last_error) {
        add_notice('error',$wpdb->last_error);
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
    foreach ( ['ability_type','usage_type','apply_to_type','apply_to_scope'] as $def ) {
        if ( !isset($ability[$def]) )
            continue;
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
       add_notice('error',$wpdb->last_error); 
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

