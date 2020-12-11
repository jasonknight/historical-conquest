<?php
namespace HistoricalConquest;
require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-config.php');
require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php');
require_once(dirname(__DIR__) . '/core.php');
require_once(dirname(__DIR__) . '/types.php');

global $wpdb;
ob_start();
?>
$hc_cards_sql = "CREATE TABLE `hc_cards` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	`deck` VARCHAR(16) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`background_image` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`background_color` VARCHAR(255) NOT NULL DEFAULT white COLLATE 'utf8mb4_general_ci',
	`border_color` VARCHAR(255) NOT NULL DEFAULT none COLLATE 'utf8mb4_general_ci',
	`text_color` VARCHAR(255) NOT NULL DEFAULT black COLLATE 'utf8mb4_general_ci',
	`illustration` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`reference` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`reference2` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`gender` VARCHAR(12) NOT NULL DEFAULT male COLLATE 'utf8mb4_general_ci',
	`ext_id` VARCHAR(32) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`year` VARCHAR(32) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`maintype` TINYINT(4) NOT NULL DEFAULT '0',
	`continent` TINYINT(4) NOT NULL DEFAULT '0',
	`is_coastal` TINYINT(4) NOT NULL DEFAULT '0',
	`nationality` SMALLINT(6) NOT NULL DEFAULT '0',
	`religion` TINYINT(4) NOT NULL DEFAULT '0',
	`climate` TINYINT(4) NOT NULL DEFAULT '0',
	`ethnicity` TINYINT(4) NOT NULL DEFAULT '0',
	`strength` INT(11) NOT NULL DEFAULT '0',
	`defense` INT(11) NOT NULL DEFAULT '0',
	`carry_capacity` INT(11) NOT NULL DEFAULT '1',
	`when_to_play` VARCHAR(32) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`summary` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`abilities` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `maintype` (`maintype`, `climate`, `continent`, `religion`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;";
$hc_card_abilities_sql = "CREATE TABLE `hc_card_abilities` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`card_id` INT(11) NOT NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	`apply_to_type` TINYINT(4) NOT NULL DEFAULT '0',
	`apply_to_scope` TINYINT(4) NOT NULL DEFAULT '0',
	`usage_type` TINYINT(4) NOT NULL DEFAULT '0',
	`ability_type` TINYINT(4) NOT NULL DEFAULT '0',
	`affects_attribute` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`affect_amount` INT(11) NOT NULL DEFAULT '0',
	`apply_to_ext_ids` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`apply_to_card_types` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`ext_ids_present_in_colum` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`custom_function` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`named_function` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`charges` TINYINT(4) NOT NULL DEFAULT '0',
	`description` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `card_id` (`card_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
";
$hc_games_sql = "CREATE TABLE `hc_games` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NULL DEFAULT NULL,
	`last_turn_at` DATETIME NULL DEFAULT NULL,
	`current_player_id` INT(11) NOT NULL,
	`turn_duration_in_minutes` TINYINT(4) NOT NULL DEFAULT '0',
	`active` TINYINT(4) NOT NULL,
	`locked` TINYINT(4) NOT NULL DEFAULT '0',
	`locked_at` DATETIME NULL DEFAULT NULL,
	`winner_id` INT(11) NOT NULL,
	`created_by` INT(11) NULL DEFAULT NULL,
	`declined` SMALLINT(6) NULL DEFAULT '0',
	`round` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;";
$hc_players_sql = "CREATE TABLE `hc_players` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`game_id` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`morale` INT(11) NOT NULL,
	`age` TINYINT(4) NOT NULL,
	`can_attack` TINYINT(4) NOT NULL DEFAULT '1',
	`dirty` TINYINT(4) NOT NULL DEFAULT '0',
	`last_update_at` DATETIME NULL DEFAULT NULL,
	`deck_id` INT(11) NULL DEFAULT NULL,
	`playmat` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`abilitymat` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`damagemat` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`hand` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`drawpile` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`discardpile` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`landpile` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`current_move` INT(11) NULL DEFAULT '0',
	`max_moves` INT(11) NULL DEFAULT '3',
	`attacks` INT(11) NULL DEFAULT '0',
	`max_attacks` INT(11) NULL DEFAULT '3',
	`can_be_attacked` INT(11) NULL DEFAULT '1',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `idxes` (`game_id`, `user_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
";
$hc_players_cards_sql = "
CREATE TABLE `hc_player_cards` (
	`player_id` INT(11) NULL DEFAULT NULL,
	`card_id` INT(11) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL
	 INDEX `hcpc_idxes` (`player_id`, `card_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
";
$hc_player_decks_sql = "CREATE TABLE `hc_player_decks` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`player_id` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`card_count` SMALLINT(6) NOT NULL DEFAULT '0',
	`use_count` SMALLINT(6) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `player_id` (`player_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
";
$hc_player_decks_cards_sql = "
CREATE TABLE `hc_player_decks_cards` (
	`deck_id` INT(11) NULL DEFAULT NULL,
	`card_id` INT(11) NULL DEFAULT NULL,
	`ext_id` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	INDEX `deck_id` (`deck_id`, `card_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
";
$hc_player_transactions_sql = "CREATE TABLE `hc_player_transactions` (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`game_id` INT(11) NOT NULL,
	`player_id` INT(11) NOT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`request_json` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`response_json` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `idxes` (`game_id`, `player_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB";
$creates = [
    $hc_cards_sql,
    $hc_card_abilities_sql,
    $hc_games_sql,
    $hc_players_sql,
    $hc_players_cards_sql,
    $hc_player_decks_sql,
    $hc_player_decks_cards_sql,
    $hc_player_transactions_sql,
];

foreach ( $creates as $sql ) {
    $wpdb->query($sql);
    if ( !empty($wpdb->last_error) ) {
        die($wpdb->last_error);
    }
}
<?php


$cards = $wpdb->get_results("SELECT * FROM hc_cards",ARRAY_A);
$keys = array_keys($cards[0]);
$columns = array_map(function ($k) { return "`$k`"; },$keys);
$ints = ['id','maintype','continent','is_coastal','nationality','religion','climate','ethnicity','strength','defense'];
$percs = join(',', array_map(function ($k) use ($ints) {
    if ( in_array($k,$ints) ) {
        return "%d";
    }
    return "%s";
},$keys));

$sql = "INSERT INTO `hc_cards` (" . join(',',$columns) . ") VALUES ";
$values = [];
foreach ( $cards as $card ) {
    $vars = join(',',array_map(function ($k) {
        return "\$card['$k']"; 
    },$keys));
   $values[] = eval("return \$wpdb->prepare(\"($percs)\",$vars);"); 
}
echo "\$wpdb->query(\"$sql " . str_replace('"','\\"',join(',',$values)) . "\");" . PHP_EOL;

$cards = $wpdb->get_results("SELECT * FROM hc_card_abilities",ARRAY_A);
$keys = array_keys($cards[0]);
$columns = array_map(function ($k) { return "`$k`"; },$keys);
$ints = ['id','card_id','apply_to_type','apply_to_scope','usage_type','ability_type','affect_amount','charges'];
$percs = join(',', array_map(function ($k) use ($ints) {
    if ( in_array($k,$ints) ) {
        return "%d";
    }
    return "%s";
},$keys));

$sql = "INSERT INTO `hc_card_abilities` (" . join(',',$columns) . ") VALUES ";
$values = [];
foreach ( $cards as $card ) {
    $vars = join(',',array_map(function ($k) {
        return "\$card['$k']"; 
    },$keys));
   $values[] = eval("return \$wpdb->prepare(\"($percs)\",$vars);"); 
}
echo "\$wpdb->query(\"$sql " . str_replace('"','\"',join(',',$values)) . "\");" . "\n";




$contents = ob_get_contents();
ob_end_clean();

file_put_contents(__DIR__ . '/install.php',join('',['<?','php']) . "\n" . $contents);
