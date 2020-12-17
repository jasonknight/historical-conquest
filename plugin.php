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
require_once(__DIR__ . '/attack.php');
require_once(__DIR__ . '/ajax_functions.php');
function write_sync_report($r) {
    $fname = date("Y-m-d") . "-user-sync.log";
    $lines = "";
    foreach ( $r as $l ) {
        $lines .= "--\n$l\n--\n";
    }
    $r = "\n^^ Begin Report At: " . date("Y-m-d H:i:s") . "\n$lines^^ End\n";
    file_put_contents(__DIR__ . "/$fname",$r,FILE_APPEND);
}
function sync_users() {
    global $wpdb;
    $log = function ($msg) {
        file_put_contents(__DIR__ . '/sync.log',date("Y-m-d H:i:s - ") . print_r($msg,true) . PHP_EOL,FILE_APPEND);    
    };
    $log(__FUNCTION__);
    if ( \is_user_logged_in() && \current_user_can('administrator') && req('action') === 'hc-user-sync-seed' ) {
        $src_url = 'https://' . req('hc-return-url');
        $last_sync_date = \get_option('hc_last_user_sync_datetime');
        if ( !$last_sync_date ) {
            $last_sync_date = 'all';
        }
        $data = [
            'action' => 'hc-user-registered',
            'hc-return-url' => $src_url, 
            'hc-sync-from' => $last_sync_date,
        ];
        $r = ajax_post($data,\site_url());
        print_r($r);
        exit;
    }
    if ( req('action') === 'hc-user-sync-verify' ) {
        $log("action=" . req('action'));
        $return_url = \site_url();
        $token = md5($return_url . file_get_contents(__DIR__ . '/token.txt'));
        if ( req('hc-user-sync-token') === $token && \get_option('hc_awaiting_sync') === 'yes' ) {
            \update_option('hc_awaiting_sync','no');
            send_json(['status' => 'OK']);
        } else {
            send_json(['status' => 'KO']);
        }
        exit;
    }
    if ( req('action') === 'hc-user-registered' ) {
        $log("action=" . req('action'));
        // Okay, so we've received a notification of a new user
        // or the cronjob has hit. 
        $last_sync_date = \get_option('hc_last_user_sync_datetime');
        if ( !$last_sync_date ) {
            $last_sync_date = 'all';
        }
        $src_url = req('hc-return-url');
        $allowed_urls = array_map('trim',file(__DIR__ . '/whitelist.txt')); 
        $found = false;
        foreach ($allowed_urls as $url) {
            if ( preg_match("/$url/",$src_url) ) {
                $found = true;
            }
        }
        $report = []; 
        if ( !$found ) {
            $report[] = "$src_url Not in whitelist";
            send_json(['status' => 'KO', 'msg' => 'Not in whitelist']);
            exit;
        }
        $return_url = \site_url();
        $data = [
            'action' => 'hc-request-sync',
            'hc-user-sync-token' => md5($return_url . file_get_contents(__DIR__ . '/token.txt')),
            'hc-return-url' => $return_url,
            'hc-sync-from' => $last_sync_date, 
        ];
        \update_option('hc_awaiting_sync','yes');
        $log("posting to " . req('hc-return-url'));
        $r = ajax_post($data,req('hc-return-url'));
        if ( !is_object($r) ) {
            action_log(__FUNCTION__ . " r is not an object for hc-user-sync");
            $report[] = "r is not an object";
            send_json(['status' => 'KO', 'msg' => 'sync failed, r is not an object']);
            exit;
        }
        $log($r);
	if ( !is_array($r->body) ) {
		$dst_url = $src_url . '?' . http_build_query($data);
		$contents = file_get_contents($dst_url);
		$log("Contents[$contents]");
		$r->body = json_decode($contents,TRUE);
		if ( !is_array($r->body) ) {
			$log("Still not an array...giving up");
            		send_json(['status' => 'KO', 'msg' => 'sync failed, r->body is not an array']);
			exit;
		}
	}
        $user_sql = "INSERT INTO {$wpdb->users} (
                user_login,
                user_pass,
                user_nicename,
                user_email,
                user_url,
                user_registered,
                user_activation_key,
                user_status,
                display_name
            ) VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %d,
                %s 
            )";
        $user_emails = array_map(function ($u) use ($wpdb) { return $wpdb->prepare('%s',$u['user_email']); },$r->body['users']);

        $existing_user_emails = array_map(function ($r) {
            return $r->user_email;
        }, $wpdb->get_results("SELECT user_email FROM {$wpdb->users} WHERE user_email IN (".join(',',$user_emails).")"));
        foreach ( $r->body['users'] as $user ) {
            if ( in_array($user['user_email'],$existing_user_emails) ) {
               $report[] = "{$user['user_email']} already exists."; 
               continue;
            }
            $user = convert_to_std($user);
            $sql = $wpdb->prepare(
                $user_sql,
                $user->user_login,
                $user->user_pass,
                $user->user_nicename,
                $user->user_email,
                $user->user_url,
                $user->user_registered,
                $user->user_activation_key,
                $user->user_status,
                $user->display_name,
            );  
            $new_last = $user->user_registered;
            $report[] = $sql;
            $wpdb->query($sql);
            if ( ! empty($wpdb->last_error) ) {
                action_log(__FUNCTION__ . ' error=' . $wpdb->last_error);
                $report[] = "{$user->user_email} error=" . $wpdb->last_error;
                write_sync_report($report);
                send_json(['status' => 'KO', 'msg' => 'failed to insert user ' . $user->user_login]);
                exit;
            }
            $user_id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
            $report[] = "{$user->user_email} now has user_id=$user_id";
            if ( is_array($user->user_metas) ) {
                $sql = "
                    INSERT INTO {$wpdb->usermeta} 
                        (
                            user_id,
                            meta_key,
                            meta_value
                        ) VALUES 
                ";
                $value_template = "(%d,%s,%s)";
                $values = [];
                foreach ( $user->user_metas as $m ) {
                    $v = $wpdb->prepare($value_template,$user_id,$m[0],$m[1]); 
		            $v = str_replace("'". $r->body['wpdb_prefix'],"'".$wpdb->prefix,$v);
		            $values[] = $v;
                }
                $sql .= join(',',$values);
                $report[] = $sql;
                $wpdb->query($sql);
                if ( !empty($wpdb->last_error) ) {
                    $report[] = $wpdb->last_error;
                    write_sync_report($report);
                    send_json(['status' => 'KO', 'msg' => 'failed to insert usermeta ' . $user->user_login]);
                    exit;
                }
            }
        }
        \update_option('hc_last_user_sync_datetime',$new_last);
        write_sync_report($report);
        send_json(['status' => 'OK','report' => $report]);
        exit;
    }
}

\add_action('template_redirect',__NAMESPACE__ . '\sync_users');
function apply_fuser() {
    $fuserkey = '_fuser';
    if ( isset($_REQUEST[$fuserkey]) ) {
        wp_set_current_user($_REQUEST[$fuserkey]);
    }
}
add_action('template_redirect',__NAMESPACE__ . '\apply_fuser',-100);
function install_game_tables() {
    global $wpdb;
    include(__DIR__ . '/tools/install.php');
    return;
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
add_action('wp_enqueue_scripts',function () {
    wp_enqueue_script( 'jquery' );
});
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
   add_shortcode('hcgame_player_cp',__NAMESPACE__ . '\shortcode_hcgame_player_cp');
   if ( get('hcgame-rules-dl') ) {
        header("Content-Type: application/pdf");
        header("Content-Disposition:attachment;filename=historical-conquest-rules.pdf");
        echo file_get_contents(__DIR__ . '/assets/historical-conquest-rule-sheet.pdf');
        exit;
   }
    ajax_init(); 
}
function assign_basic_cards_to_player($id) {
    global $wpdb;
    $sql = $wpdb->prepare(
        "INSERT INTO `hc_player_cards` 
            (SELECT %d as player_id, id as card_id, ext_id FROM `hc_cards` WHERE deck IN ('B','C'))",$id);
    $wpdb->query($sql); 
    if ( $wpdb->last_error ) {
        die($wpdb->last_error);
    }
}
function shortcode_hcgame_player_cp($attrs) {
    global $wpdb;
    // So we need to make sure the user has the base cards
    if ( !check_player_has_cards(\get_current_user_id()) ) {
        assign_basic_cards_to_player(\get_current_user_id());
    }
    $sql = "
        SELECT cards.id,cards.ext_id,cards.name FROM `hc_player_cards` as pcards 
        JOIN `hc_cards` as cards on cards.id = pcards.card_id
        WHERE
            pcards.player_id = %d
    ";
    $sql = $wpdb->prepare($sql,\get_current_user_id());
    $owned_cards = $wpdb->get_results($sql,ARRAY_A);
    echo render_template('player-control-panel.php',['owned_cards' => $owned_cards]);
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
    if ( !is_user_logged_in() ) {
        wp_redirect( wp_login_url(),302,basename(__DIR__));
        exit;
    }
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

