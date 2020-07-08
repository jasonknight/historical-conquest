CREATE TABLE `hc_players` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`game_id` INT NOT NULL,
	`user_id` INT NOT NULL,
	`name` VARCHAR(255) NOT NULL DEFAULT '0', 
	`morale` INT NOT NULL,
	`age` TINYINT NOT NULL,
	`can_attack` TINYINT NOT NULL DEFAULT '1',
	`dirty` TINYINT NOT NULL DEFAULT '0',
	`last_update_at` DATETIME,
	PRIMARY KEY (`id`),
    INDEX `idxes` (`game_id`,`user_id`)
);
CREATE TABLE `hc_player_transactions` (
	`id` BIGINT NOT NULL AUTO_INCREMENT,
	`game_id` INT NOT NULL,
	`player_id` INT NOT NULL,
	`created_at` DATETIME,
    `request_json` TEXT,
    `response_json` TEXT,
	PRIMARY KEY (`id`),
    INDEX `idxes` (`game_id`,`player_id`)
);
CREATE TABLE `hc_games` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME,
	`last_turn_at` DATETIME,
	`current_player_id` INT NOT NULL,
	`turn_duration_in_minutes` TINYINT NOT NULL DEFAULT '0',
	`active` TINYINT NOT NULL,
	`locked` TINYINT NOT NULL DEFAULT '0',
	`locked_at` DATETIME,
	`winner_id` INT NOT NULL,
    `playmats` TEXT,
    `effectmats` TEXT,
	PRIMARY KEY (`id`)
);
CREATE TABLE `hc_cards` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME,
	`updated_at` DATETIME,
	`deck` VARCHAR(16) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL DEFAULT '0',
	`background_image` VARCHAR(255) NOT NULL DEFAULT '0',
	`background_color` VARCHAR(255) NOT NULL DEFAULT 'white',
	`border_color` VARCHAR(255) NOT NULL DEFAULT 'none',
	`text_color` VARCHAR(255) NOT NULL DEFAULT 'black',
	`illustration` VARCHAR(255) NOT NULL DEFAULT '0',
	`reference` VARCHAR(255) NOT NULL DEFAULT '0',
	`reference2` VARCHAR(255) NOT NULL DEFAULT '0',
	`gender` VARCHAR(12) NOT NULL DEFAULT 'male',
	`ext_id` VARCHAR(32) NOT NULL DEFAULT '0',
	`year` VARCHAR(32) NOT NULL DEFAULT '0',
	`maintype` TINYINT NOT NULL DEFAULT '0',
	`continent` TINYINT NOT NULL DEFAULT '0',
	`religion` TINYINT NOT NULL DEFAULT '0',
	`climate` TINYINT NOT NULL DEFAULT '0',
	`ethnicity` TINYINT NOT NULL DEFAULT '0',
	`strength` INT NOT NULL DEFAULT '0',
	`defense` INT NOT NULL DEFAULT '0',
	`carry_capacity` INT NOT NULL DEFAULT '1',
	`when_to_play` VARCHAR(32) NOT NULL DEFAULT '0',
	`summary` TEXT,
	`abilities` TEXT,
	PRIMARY KEY (`id`),
	INDEX `maintype` (`maintype`, `climate`, `continent`, `religion`)
);
CREATE TABLE `hc_card_abilities` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`card_id` INT NOT NULL DEFAULT '0',
	`created_at` DATETIME,
	`updated_at` DATETIME,
	`apply_to_type` TINYINT NOT NULL DEFAULT 0,
	`apply_to_scope` TINYINT NOT NULL DEFAULT 0,
	`affects_attribute` VARCHAR(50) NOT NULL DEFAULT '0',
	`affect_amount` INT NOT NULL DEFAULT 0,
	`apply_to_ext_ids` TEXT,
	`apply_to_card_types` TEXT,
	`ext_ids_present_in_colum` TEXT,
	`custom_function` TEXT,
	`named_function` VARCHAR(50),
	`charges` TINYINT NOT NULL DEFAULT '0',
	`description` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `card_id` (`card_id`)
);
ALTER TABLE `hc_card_abilities` ADD COLUMN `ability_type` TINYINT NOT NULL DEFAULT '0' AFTER `apply_to_scope`;
ALTER TABLE `hc_card_abilities` ADD COLUMN `usage_type` TINYINT NOT NULL DEFAULT '0' AFTER `apply_to_scope`;
