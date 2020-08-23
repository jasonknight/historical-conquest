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
ALTER TABLE `hc_cards` ADD COLUMN `is_coastal` TINYINT NOT NULL DEFAULT '0' AFTER `continent`;
ALTER TABLE `hc_cards` ADD COLUMN `nationality` SMALLINT NOT NULL DEFAULT '0' AFTER `is_coastal`;
UPDATE `hc_cards` set carry_capacity = 4 WHERE maintype = 0; 
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4101';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4102';
UPDATE `hc_cards` SET continent = 82, is_coastal = 1 WHERE ext_id = 'CT4103';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4104';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4105';
UPDATE `hc_cards` SET continent = 85, is_coastal = 1 WHERE ext_id = 'CT4106';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4201';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4202';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4203';
UPDATE `hc_cards` SET continent = 82, is_coastal = 1 WHERE ext_id = 'CT4204';
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4205';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4206';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4301';
UPDATE `hc_cards` SET continent = 82, is_coastal = 1 WHERE ext_id = 'CT4302';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4303';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4304';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4305';
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4306';
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4401';
UPDATE `hc_cards` SET continent = 82, is_coastal = 1 WHERE ext_id = 'CT4402';
UPDATE `hc_cards` SET continent = 85, is_coastal = 1 WHERE ext_id = 'CT4403';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4404';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4405';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4406';
UPDATE `hc_cards` SET continent = 85, is_coastal = 1 WHERE ext_id = 'CT4501';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4502';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4503';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4504';
UPDATE `hc_cards` SET continent = 79, is_coastal = 0 WHERE ext_id = 'CT4505';
UPDATE `hc_cards` SET continent = 82, is_coastal = 1 WHERE ext_id = 'CT4506';
UPDATE `hc_cards` SET continent = 81, is_coastal = 1 WHERE ext_id = 'CT4601';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4602';
UPDATE `hc_cards` SET continent = 82, is_coastal = 0 WHERE ext_id = 'CT4603';
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4604';
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4605';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4606';
UPDATE `hc_cards` SET continent = 82, is_coastal = 1 WHERE ext_id = 'CT4701';
UPDATE `hc_cards` SET continent = 83, is_coastal = 1 WHERE ext_id = 'CT4702';
UPDATE `hc_cards` SET continent = 79, is_coastal = 1 WHERE ext_id = 'CT4703';
UPDATE `hc_cards` SET continent = 80, is_coastal = 1 WHERE ext_id = 'CT4704';
UPDATE `hc_cards` SET continent = 85, is_coastal = 1 WHERE ext_id = 'CT4705';
UPDATE `hc_cards` SET continent = 81, is_coastal = 0 WHERE ext_id = 'CT4706';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARRR401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'ARRR401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CORR401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'CORR401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CORR402';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'CORR402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MLRR401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'MLRR401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PIRR401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'PIRR401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'POE001';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'POE001';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAE002';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'WAE002';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAE003';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'WAE003';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARRW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARRW402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARRW403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW405';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW405';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW406';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW406';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW407';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW407';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LERW408';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LERW408';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SYRW401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SYRW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WARW401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WARW401';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'ARW2401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARW2402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARW2403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ARW2404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW2401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW2401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW2402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW2402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW2403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW2403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW2404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW2404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MAW2401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MUW2401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'MUW2401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAW2401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WAW2401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LECW401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LECW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LECW402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LECW402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LECW403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LECW403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LECW404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LECW404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LECW405';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LECW405';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LAWW401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LAWW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LAWW402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LAWW402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LAWW403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LAWW403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LAWW404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LAWW404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEWW401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEWW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OUWW401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OUWW402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'OUWW402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OUWW403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'OUWW403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OUWW404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'OUWW404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OUWW405';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'OUWW405';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'ASW1401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW1401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW1401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW1402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW1402';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'LEW1403';
UPDATE `hc_cards` SET religion = 72 WHERE ext_id = 'LEW1403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LEW1404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LEW1404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAW1401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WAW1401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAW1402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WAW1402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAW1403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WAW1403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAW1404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WAW1404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WAW1405';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WAW1405';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = '710000';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = '710000';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = '710001';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = '710001';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4081';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4081';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4082';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4082';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4083';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4083';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4084';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4084';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4085';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4085';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4086';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4086';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4087';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4087';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4088';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'BU4088';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4081';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4081';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4082';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4082';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4083';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4083';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4084';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4084';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4085';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4085';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4086';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4086';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4087';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4087';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4091';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'AU4091';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'CO4091';
UPDATE `hc_cards` SET religion = 74 WHERE ext_id = 'CO4091';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4091';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'LE4091';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4091';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4091';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4092';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4092';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4093';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4093';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4094';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4094';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP4091';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SP4091';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP4092';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'SP4092';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AC4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AC4102';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4102';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AI4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4101';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4102';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4104';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AT4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AT4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4101';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'AU4102';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'BU4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4101';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'CO4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4102';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'CO4102';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'CO4103';
UPDATE `hc_cards` SET religion = 74 WHERE ext_id = 'CO4103';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EC4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EC4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4102';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4102';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4103';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'EX4103';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4104';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'EX4104';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4105';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4105';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4102';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4102';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'LE4103';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'LE4104';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MU4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'MU4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PH4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PI4101';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'PO4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PO4101';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'SC4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4102';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4102';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SP4101';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'SY4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SY4101';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'WA4101';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WA4101';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AC4201';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AC4202';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4202';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4201';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'AI4201';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AR4201';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'AR4202';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4203';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4204';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AS4201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AT4201';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AT4201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4201';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4201';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AU4202';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4201';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4201';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'C04201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4202';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'CO4202';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4203';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'CO4203';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EC4201';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'EC4201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4201';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4202';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4202';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4203';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4203';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4204';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4204';
UPDATE `hc_cards` SET religion = 72 WHERE ext_id = 'EX4205';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4201';
UPDATE `hc_cards` SET religion = 100 WHERE ext_id = 'IN4201';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4202';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4202';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4201';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4201';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'LE4202';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'LE4202';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'LE4203';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'LE4204';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MU2001';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'MU2001';
UPDATE `hc_cards` SET ethnicity = 94 WHERE ext_id = 'OU3003';
UPDATE `hc_cards` SET religion = 77 WHERE ext_id = 'OU3003';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PH3001';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'PH3001';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'PO3002';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC3005';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'SC3005';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP3004';
UPDATE `hc_cards` SET religion = 76 WHERE ext_id = 'SP3004';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'WA3004';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'WA3004';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AC4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4301';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AC4302';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4302';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4301';
UPDATE `hc_cards` SET religion = 77 WHERE ext_id = 'AI4301';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4301';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4302';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AR4303';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'AR4304';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'AT4301';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'AT4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4302';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'AU4302';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4302';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'CO4302';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'CO4303';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'CO4303';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EC4301';
UPDATE `hc_cards` SET religion = 78 WHERE ext_id = 'EC4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4302';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4302';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4303';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4303';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4304';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'EX4304';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4305';
UPDATE `hc_cards` SET religion = 70 WHERE ext_id = 'EX4305';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4301';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'IN4302';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4302';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4302';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'LE4302';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'LE4303';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'LE4303';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4304';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4304';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MU4301';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'MU4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OU4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'OU4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PH4301';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'PH4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PO4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PO4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4301';
UPDATE `hc_cards` SET religion = 100 WHERE ext_id = 'SC4301';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SP4301';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'SY4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SY4301';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'WA4301';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WA4301';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AC4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4401';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'AC4402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'AI4401';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'AR4401';
UPDATE `hc_cards` SET religion = 72 WHERE ext_id = 'AR4401';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'AR4402';
UPDATE `hc_cards` SET religion = 72 WHERE ext_id = 'AR4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AR4404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AT4401';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'AT4401';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AU4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'CO4401';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'CO4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4403';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'CO4403';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'EC4401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'EC4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4405';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4405';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4402';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4401';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'LE4402';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4403';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4403';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4404';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4404';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MU4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'MU4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PH4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PI4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PI4401';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'PO4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PO4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP3007';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SP3007';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SY4401';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SY4401';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WA4401';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'WA4401';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AC4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4501';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'AC4502';
UPDATE `hc_cards` SET religion = 72 WHERE ext_id = 'AC4502';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AI4501';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4501';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'AR4502';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4503';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4504';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AS4501';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AT4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AT4501';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AU4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4501';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4502';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4502';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4501';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4501';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'CO4501';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'CO4502';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'CO4502';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4503';
UPDATE `hc_cards` SET religion = 70 WHERE ext_id = 'CO4503';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EC4501';
UPDATE `hc_cards` SET religion = 77 WHERE ext_id = 'EC4501';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4501';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4502';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4502';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4503';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4503';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4504';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4504';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4505';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4505';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4501';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4501';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4502';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4502';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'LE4501';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'LE4501';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'LE4502';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'LE4502';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4503';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4503';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4504';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4504';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'MU3004';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'MU3004';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PH3007';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PI3002';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PI3002';
UPDATE `hc_cards` SET ethnicity = 94 WHERE ext_id = 'PO2002';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PO2002';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC2001';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC2001';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SP3002';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SP3002';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WA4501';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'WA4501';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'AC4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4601';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AC4602';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4602';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4601';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'AI4601';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'AR4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4602';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4603';
UPDATE `hc_cards` SET ethnicity = 94 WHERE ext_id = 'AR4604';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AT4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AT4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4602';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4602';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'BU4601';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'CO3015';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'CO3015';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'CO3016';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'CO3016';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO3017';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'CO3017';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EC4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EC4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4602';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4602';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4603';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4603';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4604';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4604';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4602';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4602';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4602';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4602';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4603';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4603';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4604';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4604';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MU4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'MU4601';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'PH4601';
UPDATE `hc_cards` SET religion = 75 WHERE ext_id = 'PH4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PI4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PI4601';
UPDATE `hc_cards` SET ethnicity = 94 WHERE ext_id = 'PO4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PO4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4601';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'SP4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SP4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SY4601';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SY4601';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WA3006';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WA3006';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AC4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AC4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AI4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AI4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AR4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AR4701';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4702';
UPDATE `hc_cards` SET religion = 102 WHERE ext_id = 'AR4702';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4703';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AR4704';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'AS4701';
UPDATE `hc_cards` SET religion = 76 WHERE ext_id = 'AS4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AT4701';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'AT4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4701';
UPDATE `hc_cards` SET religion = 69 WHERE ext_id = 'AU4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'AU4702';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'AU4702';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'BU4701';
UPDATE `hc_cards` SET religion = 78 WHERE ext_id = 'BU4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4701';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'CO4702';
UPDATE `hc_cards` SET religion = 101 WHERE ext_id = 'CO4702';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'CO4703';
UPDATE `hc_cards` SET religion = 103 WHERE ext_id = 'CO4703';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'CO4704';
UPDATE `hc_cards` SET religion = 99 WHERE ext_id = 'CO4704';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'EX4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4701';
UPDATE `hc_cards` SET ethnicity = 92 WHERE ext_id = 'EX4702';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'EX4702';
UPDATE `hc_cards` SET ethnicity = 96 WHERE ext_id = 'EX4703';
UPDATE `hc_cards` SET ethnicity = 95 WHERE ext_id = 'EX4704';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'IN4702';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'IN4702';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4702';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4702';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4703';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'LE4704';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'LE4704';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MA4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'MA4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'MU4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'OU4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PH4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PH4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'PO4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'PO4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'SC4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'SC4701';
UPDATE `hc_cards` SET ethnicity = 93 WHERE ext_id = 'SP4701';
UPDATE `hc_cards` SET religion = 72 WHERE ext_id = 'SP4701';
UPDATE `hc_cards` SET ethnicity = 91 WHERE ext_id = 'WA4701';
UPDATE `hc_cards` SET religion = 68 WHERE ext_id = 'WA4701';
