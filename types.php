<?php
namespace HistoricalConquest;
define('NONE',-1);
define('CARD_LAND',0);
define('CARD_CHARACTER',1);
define('CARD_CHARACTER_ACTIVIST',2);
define('CARD_CHARACTER_ARTIST',3);
define('CARD_CHARACTER_ASSASSIN',4);
define('CARD_CHARACTER_ASSASSIN_SPY',5);
define('CARD_CHARACTER_ATHLETE',6);
define('CARD_CHARACTER_AUTHOR',7);
define('CARD_CHARACTER_BUSINESSMAN',8);
define('CARD_CHARACTER_CONQUEROR',9);
define('CARD_CHARACTER_ENTERTAINER',10);
define('CARD_CHARACTER_ECONOMIST',11);
define('CARD_CHARACTER_EXPLORER',12);
define('CARD_CHARACTER_EXPLORER_SEA',13);
define('CARD_CHARACTER_EXPLORER_AIR',14);
define('CARD_CHARACTER_EXPLORER_LAND',15);
define('CARD_CHARACTER_EXPLORER_LAND_SEA',16);
define('CARD_CHARACTER_EXPLORER_SPACE',17);
define('CARD_CHARACTER_INVENTOR',18);
define('CARD_CHARACTER_LAW_ENFORCEMENT',19);
define('CARD_CHARACTER_LEADER',20);
define('CARD_CHARACTER_MUSICIAN',21);
define('CARD_CHARACTER_MATHEMATICIAN',22);
define('CARD_CHARACTER_OUTLAW',23);
define('CARD_CHARACTER_OUTLAW_PIRATE',24);
define('CARD_CHARACTER_OUTLAW_MOBSTER',25);
define('CARD_CHARACTER_OUTLAW_REBEL',26);
define('CARD_CHARACTER_PHILOSOPHER',27);
define('CARD_CHARACTER_POLITICIAN',28);
define('CARD_CHARACTER_SCIENTIST',29);
define('CARD_CHARACTER_SPIRITUAL_LEADER',30);
define('CARD_CHARACTER_SPY',31);
define('CARD_CHARACTER_WARRIOR',32);
define('CARD_ARMY',33);
define('CARD_VESSEL',34);
define('CARD_AIRCRAFT',35);
define('CARD_EVENT',36);
define('CARD_KNOWLEDGE',37);
define('CARD_ORGANIZATION',38);
define('CARD_TECHNOLOGY',39);
define('CARD_LOCATION',40);
define('CARD_DOCUMENT',41);
define('CARD_RELIC',42);
define('CARD_MATHEMATICIANS',43);
define('SCOPE_ALWAYS_ON',44);
define('SCOPE_ANYTIME',45);
define('SCOPE_TURN',46);
define('SCOPE_ATTACK',47);
define('SCOPE_DEFENSE',48);
define('APPLY_PLAYER',49);
define('APPLY_OPPONENT',50);
define('APPLY_ALL_OPPONENTS',51);
define('APPLY_CARD_IN_SAME_COLUMN',52);
define('APPLY_CARD_PLAYED',53);
define('APPLY_CARDS_IN_COLUMN',54);
define('APPLY_CARDS_PLAYED',55);
define('APPLY_GENDER_IN_SAME_COLUMN',56);
define('APPLY_RELIGION_IN_SAME_COLUMN',57);
define('APPLY_OPPONENT_CARD_IN_SAME_COLUMN',58);
define('APPLY_OPPONENT_CARD_PLAYED',59);
define('APPLY_OPPONENT_CARDS_IN_COLUMN',60);
define('APPLY_OPPONENT_CARDS_PLAYED',61);
define('USAGE_IMMEDIATE',62);
define('USAGE_HOLD',63);
define('USAGE_CONSTANT',64);
define('ABILITY_INTERRUPT',65);
define('ABILITY_CHOICE',66);
define('ABILITY_MULTI',67);
define('RELIGION_CHRISTIAN',68);
define('RELIGION_CATHOLIC',69);
define('RELIGION_ORTHODOX_CHRISTIAN',70);
define('RELIGION_PROTESTANT',71);
define('RELIGION_MUSLIM',72);
define('RELIGION_MUSLIM_SHIITE',73);
define('RELIGION_MUSLIM_SUNNI',74);
define('RELIGION_BUDDHIST',75);
define('RELIGION_HINDU',76);
define('RELIGION_ATHEIST',77);
define('RELIGION_JEWISH',78);
define('CONTINENT_NORTH_AMERICA',79);
define('CONTINENT_ASIA',80);
define('CONTINENT_EUROPE',81);
define('CONTINENT_AFRICA',82);
define('CONTINENT_SOUTH_AMERICA',83);
define('CONTINENT_ANTARCTICA',84);
define('CONTINENT_OCEANIA',85);
define('CLIMATE_TROPICAL',86);
define('CLIMATE_DRY',87);
define('CLIMATE_TEMPERATE',88);
define('CLIMATE_CONTINENTAL',89);
define('CLIMATE_POLAR',90);
define('ETH_WHITE',91);
define('ETH_BLACK',92);
define('ETH_ARAB',93);
define('ETH_HISPANIC',94);
define('ETH_ASIAN',95);
define('ETH_IDIGENOUS',96);
define('ETH_JEWISH',97);
define('USAGE_ONCE',98);
define('RELIGION_PAGAN',99);
define('RELIGION_AGNOSTIC',100);
define('RELIGION_TAOIST',101);
define('RELIGION_SHINTO',102);
define('RELIGION_ZOROASTRIAN',103);
function type_to_name($val) { 
	 if ( $val == CARD_LAND ) { return 'CARD_LAND'; }
	 if ( $val == CARD_CHARACTER_ACTIVIST ) { return 'CARD_CHARACTER_ACTIVIST'; }
	 if ( $val == CARD_CHARACTER_ARTIST ) { return 'CARD_CHARACTER_ARTIST'; }
	 if ( $val == CARD_CHARACTER_ASSASSIN_SPY ) { return 'CARD_CHARACTER_ASSASSIN_SPY'; }
	 if ( $val == CARD_CHARACTER_ATHLETE ) { return 'CARD_CHARACTER_ATHLETE'; }
	 if ( $val == CARD_CHARACTER_AUTHOR ) { return 'CARD_CHARACTER_AUTHOR'; }
	 if ( $val == CARD_CHARACTER_BUSINESSMAN ) { return 'CARD_CHARACTER_BUSINESSMAN'; }
	 if ( $val == CARD_CHARACTER_CONQUEROR ) { return 'CARD_CHARACTER_CONQUEROR'; }
	 if ( $val == CARD_CHARACTER_ENTERTAINER ) { return 'CARD_CHARACTER_ENTERTAINER'; }
	 if ( $val == CARD_CHARACTER_ECONOMIST ) { return 'CARD_CHARACTER_ECONOMIST'; }
	 if ( $val == CARD_CHARACTER_EXPLORER_SEA ) { return 'CARD_CHARACTER_EXPLORER_SEA'; }
	 if ( $val == CARD_CHARACTER_EXPLORER_AIR ) { return 'CARD_CHARACTER_EXPLORER_AIR'; }
	 if ( $val == CARD_CHARACTER_EXPLORER_LAND ) { return 'CARD_CHARACTER_EXPLORER_LAND'; }
	 if ( $val == CARD_CHARACTER_EXPLORER_LAND_SEA ) { return 'CARD_CHARACTER_EXPLORER_LAND_SEA'; }
	 if ( $val == CARD_CHARACTER_EXPLORER_SPACE ) { return 'CARD_CHARACTER_EXPLORER_SPACE'; }
	 if ( $val == CARD_CHARACTER_INVENTOR ) { return 'CARD_CHARACTER_INVENTOR'; }
	 if ( $val == CARD_CHARACTER_LAW_ENFORCEMENT ) { return 'CARD_CHARACTER_LAW_ENFORCEMENT'; }
	 if ( $val == CARD_CHARACTER_LEADER ) { return 'CARD_CHARACTER_LEADER'; }
	 if ( $val == CARD_CHARACTER_MUSICIAN ) { return 'CARD_CHARACTER_MUSICIAN'; }
	 if ( $val == CARD_CHARACTER_MATHEMATICIAN ) { return 'CARD_CHARACTER_MATHEMATICIAN'; }
	 if ( $val == CARD_CHARACTER_OUTLAW_PIRATE ) { return 'CARD_CHARACTER_OUTLAW_PIRATE'; }
	 if ( $val == CARD_CHARACTER_OUTLAW_MOBSTER ) { return 'CARD_CHARACTER_OUTLAW_MOBSTER'; }
	 if ( $val == CARD_CHARACTER_OUTLAW_REBEL ) { return 'CARD_CHARACTER_OUTLAW_REBEL'; }
	 if ( $val == CARD_CHARACTER_PHILOSOPHER ) { return 'CARD_CHARACTER_PHILOSOPHER'; }
	 if ( $val == CARD_CHARACTER_POLITICIAN ) { return 'CARD_CHARACTER_POLITICIAN'; }
	 if ( $val == CARD_CHARACTER_SCIENTIST ) { return 'CARD_CHARACTER_SCIENTIST'; }
	 if ( $val == CARD_CHARACTER_SPIRITUAL_LEADER ) { return 'CARD_CHARACTER_SPIRITUAL_LEADER'; }
	 if ( $val == CARD_CHARACTER_SPY ) { return 'CARD_CHARACTER_SPY'; }
	 if ( $val == CARD_CHARACTER_WARRIOR ) { return 'CARD_CHARACTER_WARRIOR'; }
	 if ( $val == CARD_ARMY ) { return 'CARD_ARMY'; }
	 if ( $val == CARD_VESSEL ) { return 'CARD_VESSEL'; }
	 if ( $val == CARD_AIRCRAFT ) { return 'CARD_AIRCRAFT'; }
	 if ( $val == CARD_EVENT ) { return 'CARD_EVENT'; }
	 if ( $val == CARD_KNOWLEDGE ) { return 'CARD_KNOWLEDGE'; }
	 if ( $val == CARD_ORGANIZATION ) { return 'CARD_ORGANIZATION'; }
	 if ( $val == CARD_TECHNOLOGY ) { return 'CARD_TECHNOLOGY'; }
	 if ( $val == CARD_LOCATION ) { return 'CARD_LOCATION'; }
	 if ( $val == CARD_DOCUMENT ) { return 'CARD_DOCUMENT'; }
	 if ( $val == CARD_RELIC ) { return 'CARD_RELIC'; }
	 if ( $val == CARD_MATHEMATICIANS ) { return 'CARD_MATHEMATICIANS'; }
	 if ( $val == SCOPE_ALWAYS_ON ) { return 'SCOPE_ALWAYS_ON'; }
	 if ( $val == SCOPE_ANYTIME ) { return 'SCOPE_ANYTIME'; }
	 if ( $val == SCOPE_TURN ) { return 'SCOPE_TURN'; }
	 if ( $val == SCOPE_ATTACK ) { return 'SCOPE_ATTACK'; }
	 if ( $val == SCOPE_DEFENSE ) { return 'SCOPE_DEFENSE'; }
	 if ( $val == APPLY_PLAYER ) { return 'APPLY_PLAYER'; }
	 if ( $val == APPLY_OPPONENT ) { return 'APPLY_OPPONENT'; }
	 if ( $val == APPLY_ALL_OPPONENTS ) { return 'APPLY_ALL_OPPONENTS'; }
	 if ( $val == APPLY_CARD_IN_SAME_COLUMN ) { return 'APPLY_CARD_IN_SAME_COLUMN'; }
	 if ( $val == APPLY_CARD_PLAYED ) { return 'APPLY_CARD_PLAYED'; }
	 if ( $val == APPLY_CARDS_IN_COLUMN ) { return 'APPLY_CARDS_IN_COLUMN'; }
	 if ( $val == APPLY_CARDS_PLAYED ) { return 'APPLY_CARDS_PLAYED'; }
	 if ( $val == APPLY_GENDER_IN_SAME_COLUMN ) { return 'APPLY_GENDER_IN_SAME_COLUMN'; }
	 if ( $val == APPLY_RELIGION_IN_SAME_COLUMN ) { return 'APPLY_RELIGION_IN_SAME_COLUMN'; }
	 if ( $val == APPLY_OPPONENT_CARD_IN_SAME_COLUMN ) { return 'APPLY_OPPONENT_CARD_IN_SAME_COLUMN'; }
	 if ( $val == APPLY_OPPONENT_CARD_PLAYED ) { return 'APPLY_OPPONENT_CARD_PLAYED'; }
	 if ( $val == APPLY_OPPONENT_CARDS_IN_COLUMN ) { return 'APPLY_OPPONENT_CARDS_IN_COLUMN'; }
	 if ( $val == APPLY_OPPONENT_CARDS_PLAYED ) { return 'APPLY_OPPONENT_CARDS_PLAYED'; }
	 if ( $val == USAGE_IMMEDIATE ) { return 'USAGE_IMMEDIATE'; }
	 if ( $val == USAGE_HOLD ) { return 'USAGE_HOLD'; }
	 if ( $val == USAGE_CONSTANT ) { return 'USAGE_CONSTANT'; }
	 if ( $val == USAGE_ONCE ) { return 'USAGE_ONCE'; }
	 if ( $val == ABILITY_INTERRUPT ) { return 'ABILITY_INTERRUPT'; }
	 if ( $val == ABILITY_CHOICE ) { return 'ABILITY_CHOICE'; }
	 if ( $val == ABILITY_MULTI ) { return 'ABILITY_MULTI'; }
	 if ( $val == RELIGION_CHRISTIAN ) { return 'RELIGION_CHRISTIAN'; }
	 if ( $val == RELIGION_CATHOLIC ) { return 'RELIGION_CATHOLIC'; }
	 if ( $val == RELIGION_ORTHODOX_CHRISTIAN ) { return 'RELIGION_ORTHODOX_CHRISTIAN'; }
	 if ( $val == RELIGION_PROTESTANT ) { return 'RELIGION_PROTESTANT'; }
	 if ( $val == RELIGION_MUSLIM ) { return 'RELIGION_MUSLIM'; }
	 if ( $val == RELIGION_MUSLIM_SHIITE ) { return 'RELIGION_MUSLIM_SHIITE'; }
	 if ( $val == RELIGION_MUSLIM_SUNNI ) { return 'RELIGION_MUSLIM_SUNNI'; }
	 if ( $val == RELIGION_BUDDHIST ) { return 'RELIGION_BUDDHIST'; }
	 if ( $val == RELIGION_HINDU ) { return 'RELIGION_HINDU'; }
	 if ( $val == RELIGION_ATHEIST ) { return 'RELIGION_ATHEIST'; }
	 if ( $val == RELIGION_JEWISH ) { return 'RELIGION_JEWISH'; }
	 if ( $val == RELIGION_SHINTO ) { return 'RELIGION_SHINTO'; }
	 if ( $val == RELIGION_AGNOSTIC ) { return 'RELIGION_AGNOSTIC'; }
	 if ( $val == RELIGION_PAGAN ) { return 'RELIGION_PAGAN'; }
	 if ( $val == RELIGION_TAOIST ) { return 'RELIGION_TAOIST'; }
	 if ( $val == RELIGION_ZOROASTRIAN ) { return 'RELIGION_ZOROASTRIAN'; }
	 if ( $val == CONTINENT_NORTH_AMERICA ) { return 'CONTINENT_NORTH_AMERICA'; }
	 if ( $val == CONTINENT_ASIA ) { return 'CONTINENT_ASIA'; }
	 if ( $val == CONTINENT_EUROPE ) { return 'CONTINENT_EUROPE'; }
	 if ( $val == CONTINENT_AFRICA ) { return 'CONTINENT_AFRICA'; }
	 if ( $val == CONTINENT_SOUTH_AMERICA ) { return 'CONTINENT_SOUTH_AMERICA'; }
	 if ( $val == CONTINENT_ANTARCTICA ) { return 'CONTINENT_ANTARCTICA'; }
	 if ( $val == CONTINENT_OCEANIA ) { return 'CONTINENT_OCEANIA'; }
	 if ( $val == CLIMATE_TROPICAL ) { return 'CLIMATE_TROPICAL'; }
	 if ( $val == CLIMATE_DRY ) { return 'CLIMATE_DRY'; }
	 if ( $val == CLIMATE_TEMPERATE ) { return 'CLIMATE_TEMPERATE'; }
	 if ( $val == CLIMATE_CONTINENTAL ) { return 'CLIMATE_CONTINENTAL'; }
	 if ( $val == CLIMATE_POLAR ) { return 'CLIMATE_POLAR'; }
	 if ( $val == ETH_WHITE ) { return 'ETH_WHITE'; }
	 if ( $val == ETH_BLACK ) { return 'ETH_BLACK'; }
	 if ( $val == ETH_ARAB ) { return 'ETH_ARAB'; }
	 if ( $val == ETH_HISPANIC ) { return 'ETH_HISPANIC'; }
	 if ( $val == ETH_ASIAN ) { return 'ETH_ASIAN'; }
	 if ( $val == ETH_IDIGENOUS ) { return 'ETH_IDIGENOUS'; }
	 if ( $val == ETH_JEWISH ) { return 'ETH_JEWISH'; }
}
function name_to_type($val) { 
	 if ( $val == 'CARD_LAND' ) { return CARD_LAND; }
	 if ( $val == 'CARD_CHARACTER_ACTIVIST' ) { return CARD_CHARACTER_ACTIVIST; }
	 if ( $val == 'CARD_CHARACTER_ARTIST' ) { return CARD_CHARACTER_ARTIST; }
	 if ( $val == 'CARD_CHARACTER_ASSASSIN_SPY' ) { return CARD_CHARACTER_ASSASSIN_SPY; }
	 if ( $val == 'CARD_CHARACTER_ATHLETE' ) { return CARD_CHARACTER_ATHLETE; }
	 if ( $val == 'CARD_CHARACTER_AUTHOR' ) { return CARD_CHARACTER_AUTHOR; }
	 if ( $val == 'CARD_CHARACTER_BUSINESSMAN' ) { return CARD_CHARACTER_BUSINESSMAN; }
	 if ( $val == 'CARD_CHARACTER_CONQUEROR' ) { return CARD_CHARACTER_CONQUEROR; }
	 if ( $val == 'CARD_CHARACTER_ENTERTAINER' ) { return CARD_CHARACTER_ENTERTAINER; }
	 if ( $val == 'CARD_CHARACTER_ECONOMIST' ) { return CARD_CHARACTER_ECONOMIST; }
	 if ( $val == 'CARD_CHARACTER_EXPLORER_SEA' ) { return CARD_CHARACTER_EXPLORER_SEA; }
	 if ( $val == 'CARD_CHARACTER_EXPLORER_AIR' ) { return CARD_CHARACTER_EXPLORER_AIR; }
	 if ( $val == 'CARD_CHARACTER_EXPLORER_LAND' ) { return CARD_CHARACTER_EXPLORER_LAND; }
	 if ( $val == 'CARD_CHARACTER_EXPLORER_LAND_SEA' ) { return CARD_CHARACTER_EXPLORER_LAND_SEA; }
	 if ( $val == 'CARD_CHARACTER_EXPLORER_SPACE' ) { return CARD_CHARACTER_EXPLORER_SPACE; }
	 if ( $val == 'CARD_CHARACTER_INVENTOR' ) { return CARD_CHARACTER_INVENTOR; }
	 if ( $val == 'CARD_CHARACTER_LAW_ENFORCEMENT' ) { return CARD_CHARACTER_LAW_ENFORCEMENT; }
	 if ( $val == 'CARD_CHARACTER_LEADER' ) { return CARD_CHARACTER_LEADER; }
	 if ( $val == 'CARD_CHARACTER_MUSICIAN' ) { return CARD_CHARACTER_MUSICIAN; }
	 if ( $val == 'CARD_CHARACTER_MATHEMATICIAN' ) { return CARD_CHARACTER_MATHEMATICIAN; }
	 if ( $val == 'CARD_CHARACTER_OUTLAW_PIRATE' ) { return CARD_CHARACTER_OUTLAW_PIRATE; }
	 if ( $val == 'CARD_CHARACTER_OUTLAW_MOBSTER' ) { return CARD_CHARACTER_OUTLAW_MOBSTER; }
	 if ( $val == 'CARD_CHARACTER_OUTLAW_REBEL' ) { return CARD_CHARACTER_OUTLAW_REBEL; }
	 if ( $val == 'CARD_CHARACTER_PHILOSOPHER' ) { return CARD_CHARACTER_PHILOSOPHER; }
	 if ( $val == 'CARD_CHARACTER_POLITICIAN' ) { return CARD_CHARACTER_POLITICIAN; }
	 if ( $val == 'CARD_CHARACTER_SCIENTIST' ) { return CARD_CHARACTER_SCIENTIST; }
	 if ( $val == 'CARD_CHARACTER_SPIRITUAL_LEADER' ) { return CARD_CHARACTER_SPIRITUAL_LEADER; }
	 if ( $val == 'CARD_CHARACTER_SPY' ) { return CARD_CHARACTER_SPY; }
	 if ( $val == 'CARD_CHARACTER_WARRIOR' ) { return CARD_CHARACTER_WARRIOR; }
	 if ( $val == 'CARD_ARMY' ) { return CARD_ARMY; }
	 if ( $val == 'CARD_VESSEL' ) { return CARD_VESSEL; }
	 if ( $val == 'CARD_AIRCRAFT' ) { return CARD_AIRCRAFT; }
	 if ( $val == 'CARD_EVENT' ) { return CARD_EVENT; }
	 if ( $val == 'CARD_KNOWLEDGE' ) { return CARD_KNOWLEDGE; }
	 if ( $val == 'CARD_ORGANIZATION' ) { return CARD_ORGANIZATION; }
	 if ( $val == 'CARD_TECHNOLOGY' ) { return CARD_TECHNOLOGY; }
	 if ( $val == 'CARD_LOCATION' ) { return CARD_LOCATION; }
	 if ( $val == 'CARD_DOCUMENT' ) { return CARD_DOCUMENT; }
	 if ( $val == 'CARD_RELIC' ) { return CARD_RELIC; }
	 if ( $val == 'CARD_MATHEMATICIANS' ) { return CARD_MATHEMATICIANS; }
	 if ( $val == 'SCOPE_ALWAYS_ON' ) { return SCOPE_ALWAYS_ON; }
	 if ( $val == 'SCOPE_ANYTIME' ) { return SCOPE_ANYTIME; }
	 if ( $val == 'SCOPE_TURN' ) { return SCOPE_TURN; }
	 if ( $val == 'SCOPE_ATTACK' ) { return SCOPE_ATTACK; }
	 if ( $val == 'SCOPE_DEFENSE' ) { return SCOPE_DEFENSE; }
	 if ( $val == 'APPLY_PLAYER' ) { return APPLY_PLAYER; }
	 if ( $val == 'APPLY_OPPONENT' ) { return APPLY_OPPONENT; }
	 if ( $val == 'APPLY_ALL_OPPONENTS' ) { return APPLY_ALL_OPPONENTS; }
	 if ( $val == 'APPLY_CARD_IN_SAME_COLUMN' ) { return APPLY_CARD_IN_SAME_COLUMN; }
	 if ( $val == 'APPLY_CARD_PLAYED' ) { return APPLY_CARD_PLAYED; }
	 if ( $val == 'APPLY_CARDS_IN_COLUMN' ) { return APPLY_CARDS_IN_COLUMN; }
	 if ( $val == 'APPLY_CARDS_PLAYED' ) { return APPLY_CARDS_PLAYED; }
	 if ( $val == 'APPLY_GENDER_IN_SAME_COLUMN' ) { return APPLY_GENDER_IN_SAME_COLUMN; }
	 if ( $val == 'APPLY_RELIGION_IN_SAME_COLUMN' ) { return APPLY_RELIGION_IN_SAME_COLUMN; }
	 if ( $val == 'APPLY_OPPONENT_CARD_IN_SAME_COLUMN' ) { return APPLY_OPPONENT_CARD_IN_SAME_COLUMN; }
	 if ( $val == 'APPLY_OPPONENT_CARD_PLAYED' ) { return APPLY_OPPONENT_CARD_PLAYED; }
	 if ( $val == 'APPLY_OPPONENT_CARDS_IN_COLUMN' ) { return APPLY_OPPONENT_CARDS_IN_COLUMN; }
	 if ( $val == 'APPLY_OPPONENT_CARDS_PLAYED' ) { return APPLY_OPPONENT_CARDS_PLAYED; }
	 if ( $val == 'USAGE_IMMEDIATE' ) { return USAGE_IMMEDIATE; }
	 if ( $val == 'USAGE_HOLD' ) { return USAGE_HOLD; }
	 if ( $val == 'USAGE_CONSTANT' ) { return USAGE_CONSTANT; }
	 if ( $val == 'USAGE_ONCE' ) { return USAGE_ONCE; }
	 if ( $val == 'ABILITY_INTERRUPT' ) { return ABILITY_INTERRUPT; }
	 if ( $val == 'ABILITY_CHOICE' ) { return ABILITY_CHOICE; }
	 if ( $val == 'ABILITY_MULTI' ) { return ABILITY_MULTI; }
	 if ( $val == 'RELIGION_CHRISTIAN' ) { return RELIGION_CHRISTIAN; }
	 if ( $val == 'RELIGION_CATHOLIC' ) { return RELIGION_CATHOLIC; }
	 if ( $val == 'RELIGION_ORTHODOX_CHRISTIAN' ) { return RELIGION_ORTHODOX_CHRISTIAN; }
	 if ( $val == 'RELIGION_PROTESTANT' ) { return RELIGION_PROTESTANT; }
	 if ( $val == 'RELIGION_MUSLIM' ) { return RELIGION_MUSLIM; }
	 if ( $val == 'RELIGION_MUSLIM_SHIITE' ) { return RELIGION_MUSLIM_SHIITE; }
	 if ( $val == 'RELIGION_MUSLIM_SUNNI' ) { return RELIGION_MUSLIM_SUNNI; }
	 if ( $val == 'RELIGION_BUDDHIST' ) { return RELIGION_BUDDHIST; }
	 if ( $val == 'RELIGION_HINDU' ) { return RELIGION_HINDU; }
	 if ( $val == 'RELIGION_ATHEIST' ) { return RELIGION_ATHEIST; }
	 if ( $val == 'RELIGION_JEWISH' ) { return RELIGION_JEWISH; }
     if ( $val == 'RELIGION_SHINTO' ) { return RELIGION_SHINTO; }
	 if ( $val == 'RELIGION_AGNOSTIC' ) { return RELIGION_AGNOSTIC; }
	 if ( $val == 'RELIGION_PAGAN' ) { return RELIGION_PAGAN; }
	 if ( $val == 'RELIGION_TAOIST' ) { return RELIGION_TAOIST; }
	 if ( $val == 'RELIGION_ZOROASTRIAN' ) { return RELIGION_ZOROASTRIAN; }
	 if ( $val == 'CONTINENT_NORTH_AMERICA' ) { return CONTINENT_NORTH_AMERICA; }
	 if ( $val == 'CONTINENT_ASIA' ) { return CONTINENT_ASIA; }
	 if ( $val == 'CONTINENT_EUROPE' ) { return CONTINENT_EUROPE; }
	 if ( $val == 'CONTINENT_AFRICA' ) { return CONTINENT_AFRICA; }
	 if ( $val == 'CONTINENT_SOUTH_AMERICA' ) { return CONTINENT_SOUTH_AMERICA; }
	 if ( $val == 'CONTINENT_ANTARCTICA' ) { return CONTINENT_ANTARCTICA; }
	 if ( $val == 'CONTINENT_OCEANIA' ) { return CONTINENT_OCEANIA; }
	 if ( $val == 'CLIMATE_TROPICAL' ) { return CLIMATE_TROPICAL; }
	 if ( $val == 'CLIMATE_DRY' ) { return CLIMATE_DRY; }
	 if ( $val == 'CLIMATE_TEMPERATE' ) { return CLIMATE_TEMPERATE; }
	 if ( $val == 'CLIMATE_CONTINENTAL' ) { return CLIMATE_CONTINENTAL; }
	 if ( $val == 'CLIMATE_POLAR' ) { return CLIMATE_POLAR; }
	 if ( $val == 'ETH_WHITE' ) { return ETH_WHITE; }
	 if ( $val == 'ETH_BLACK' ) { return ETH_BLACK; }
	 if ( $val == 'ETH_ARAB' ) { return ETH_ARAB; }
	 if ( $val == 'ETH_HISPANIC' ) { return ETH_HISPANIC; }
	 if ( $val == 'ETH_ASIAN' ) { return ETH_ASIAN; }
	 if ( $val == 'ETH_IDIGENOUS' ) { return ETH_IDIGENOUS; }
	 if ( $val == 'ETH_JEWISH' ) { return ETH_JEWISH; }
}
function options_by_prefix($pref) { 
	 $defs = array (
  'CARD' => 
  array (
    0 => 'CARD_LAND',
    1 => 'CARD_CHARACTER_ACTIVIST',
    2 => 'CARD_CHARACTER_ARTIST',
    3 => 'CARD_CHARACTER_ASSASSIN_SPY',
    4 => 'CARD_CHARACTER_ATHLETE',
    5 => 'CARD_CHARACTER_AUTHOR',
    6 => 'CARD_CHARACTER_BUSINESSMAN',
    7 => 'CARD_CHARACTER_CONQUEROR',
    8 => 'CARD_CHARACTER_ENTERTAINER',
    9 => 'CARD_CHARACTER_ECONOMIST',
    10 => 'CARD_CHARACTER_EXPLORER_SEA',
    11 => 'CARD_CHARACTER_EXPLORER_AIR',
    12 => 'CARD_CHARACTER_EXPLORER_LAND',
    13 => 'CARD_CHARACTER_EXPLORER_LAND_SEA',
    14 => 'CARD_CHARACTER_EXPLORER_SPACE',
    15 => 'CARD_CHARACTER_INVENTOR',
    16 => 'CARD_CHARACTER_LAW_ENFORCEMENT',
    17 => 'CARD_CHARACTER_LEADER',
    18 => 'CARD_CHARACTER_MUSICIAN',
    19 => 'CARD_CHARACTER_MATHEMATICIAN',
    20 => 'CARD_CHARACTER_OUTLAW_PIRATE',
    21 => 'CARD_CHARACTER_OUTLAW_MOBSTER',
    22 => 'CARD_CHARACTER_OUTLAW_REBEL',
    23 => 'CARD_CHARACTER_PHILOSOPHER',
    24 => 'CARD_CHARACTER_POLITICIAN',
    25 => 'CARD_CHARACTER_SCIENTIST',
    26 => 'CARD_CHARACTER_SPIRITUAL_LEADER',
    27 => 'CARD_CHARACTER_SPY',
    28 => 'CARD_CHARACTER_WARRIOR',
    29 => 'CARD_ARMY',
    30 => 'CARD_VESSEL',
    31 => 'CARD_AIRCRAFT',
    32 => 'CARD_EVENT',
    33 => 'CARD_KNOWLEDGE',
    34 => 'CARD_ORGANIZATION',
    35 => 'CARD_TECHNOLOGY',
    36 => 'CARD_LOCATION',
    37 => 'CARD_DOCUMENT',
    38 => 'CARD_RELIC',
    39 => 'CARD_MATHEMATICIANS',
  ),
  'SCOPE' => 
  array (
    0 => 'SCOPE_ALWAYS_ON',
    1 => 'SCOPE_ANYTIME',
    2 => 'SCOPE_TURN',
    3 => 'SCOPE_ATTACK',
    4 => 'SCOPE_DEFENSE',
  ),
  'APPLY' => 
  array (
    0 => 'APPLY_PLAYER',
    1 => 'APPLY_OPPONENT',
    2 => 'APPLY_ALL_OPPONENTS',
    3 => 'APPLY_CARD_IN_SAME_COLUMN',
    4 => 'APPLY_CARD_PLAYED',
    5 => 'APPLY_CARDS_IN_COLUMN',
    6 => 'APPLY_CARDS_PLAYED',
    7 => 'APPLY_GENDER_IN_SAME_COLUMN',
    8 => 'APPLY_RELIGION_IN_SAME_COLUMN',
    9 => 'APPLY_OPPONENT_CARD_IN_SAME_COLUMN',
    10 => 'APPLY_OPPONENT_CARD_PLAYED',
    11 => 'APPLY_OPPONENT_CARDS_IN_COLUMN',
    12 => 'APPLY_OPPONENT_CARDS_PLAYED',
  ),
  'USAGE' => 
  array (
    0 => 'USAGE_IMMEDIATE',
    1 => 'USAGE_HOLD',
    2 => 'USAGE_CONSTANT',
    3 => 'USAGE_ONCE',
  ),
  'ABILITY' => 
  array (
    0 => 'ABILITY_INTERRUPT',
    1 => 'ABILITY_CHOICE',
    2 => 'ABILITY_MULTI',
  ),
  'RELIGION' => 
  array (
    0 => 'RELIGION_CHRISTIAN',
    1 => 'RELIGION_CATHOLIC',
    2 => 'RELIGION_ORTHODOX_CHRISTIAN',
    3 => 'RELIGION_PROTESTANT',
    4 => 'RELIGION_MUSLIM',
    5 => 'RELIGION_MUSLIM_SHIITE',
    6 => 'RELIGION_MUSLIM_SUNNI',
    7 => 'RELIGION_BUDDHIST',
    8 => 'RELIGION_HINDU',
    9 => 'RELIGION_ATHEIST',
    10 => 'RELIGION_JEWISH',
    11 => 'RELIGION_PAGAN',
    12 => 'RELIGION_SHINTO',
    13 => 'RELIGION_TAOIST',
    14 => 'RELIGION_AGNOSTIC',
    15 => 'RELIGION_ZOROASTRIAN',
  ),
  'CONTINENT' => 
  array (
    0 => 'CONTINENT_NORTH_AMERICA',
    1 => 'CONTINENT_ASIA',
    2 => 'CONTINENT_EUROPE',
    3 => 'CONTINENT_AFRICA',
    4 => 'CONTINENT_SOUTH_AMERICA',
    5 => 'CONTINENT_ANTARCTICA',
    6 => 'CONTINENT_OCEANIA',
  ),
  'CLIMATE' => 
  array (
    0 => 'CLIMATE_TROPICAL',
    1 => 'CLIMATE_DRY',
    2 => 'CLIMATE_TEMPERATE',
    3 => 'CLIMATE_CONTINENTAL',
    4 => 'CLIMATE_POLAR',
  ),
  'ETH' => 
  array (
    0 => 'ETH_WHITE',
    1 => 'ETH_BLACK',
    2 => 'ETH_ARAB',
    3 => 'ETH_HISPANIC',
    4 => 'ETH_ASIAN',
    5 => 'ETH_IDIGENOUS',
    6 => 'ETH_JEWISH',
  ),
);

 return !empty($pref) ? $defs[$pref] : $defs;
}
function options_as_array() { 
	$defs = [];
	$defs['CARD_LAND'] = CARD_LAND;
	$defs['CARD_CHARACTER_ACTIVIST'] = CARD_CHARACTER_ACTIVIST;
	$defs['CARD_CHARACTER_ARTIST'] = CARD_CHARACTER_ARTIST;
	$defs['CARD_CHARACTER_ASSASSIN_SPY'] = CARD_CHARACTER_ASSASSIN_SPY;
	$defs['CARD_CHARACTER_ATHLETE'] = CARD_CHARACTER_ATHLETE;
	$defs['CARD_CHARACTER_AUTHOR'] = CARD_CHARACTER_AUTHOR;
	$defs['CARD_CHARACTER_BUSINESSMAN'] = CARD_CHARACTER_BUSINESSMAN;
	$defs['CARD_CHARACTER_CONQUEROR'] = CARD_CHARACTER_CONQUEROR;
	$defs['CARD_CHARACTER_ENTERTAINER'] = CARD_CHARACTER_ENTERTAINER;
	$defs['CARD_CHARACTER_ECONOMIST'] = CARD_CHARACTER_ECONOMIST;
	$defs['CARD_CHARACTER_EXPLORER_SEA'] = CARD_CHARACTER_EXPLORER_SEA;
	$defs['CARD_CHARACTER_EXPLORER_AIR'] = CARD_CHARACTER_EXPLORER_AIR;
	$defs['CARD_CHARACTER_EXPLORER_LAND'] = CARD_CHARACTER_EXPLORER_LAND;
	$defs['CARD_CHARACTER_EXPLORER_LAND_SEA'] = CARD_CHARACTER_EXPLORER_LAND_SEA;
	$defs['CARD_CHARACTER_EXPLORER_SPACE'] = CARD_CHARACTER_EXPLORER_SPACE;
	$defs['CARD_CHARACTER_INVENTOR'] = CARD_CHARACTER_INVENTOR;
	$defs['CARD_CHARACTER_LAW_ENFORCEMENT'] = CARD_CHARACTER_LAW_ENFORCEMENT;
	$defs['CARD_CHARACTER_LEADER'] = CARD_CHARACTER_LEADER;
	$defs['CARD_CHARACTER_MUSICIAN'] = CARD_CHARACTER_MUSICIAN;
	$defs['CARD_CHARACTER_MATHEMATICIAN'] = CARD_CHARACTER_MATHEMATICIAN;
	$defs['CARD_CHARACTER_OUTLAW_PIRATE'] = CARD_CHARACTER_OUTLAW_PIRATE;
	$defs['CARD_CHARACTER_OUTLAW_MOBSTER'] = CARD_CHARACTER_OUTLAW_MOBSTER;
	$defs['CARD_CHARACTER_OUTLAW_REBEL'] = CARD_CHARACTER_OUTLAW_REBEL;
	$defs['CARD_CHARACTER_PHILOSOPHER'] = CARD_CHARACTER_PHILOSOPHER;
	$defs['CARD_CHARACTER_POLITICIAN'] = CARD_CHARACTER_POLITICIAN;
	$defs['CARD_CHARACTER_SCIENTIST'] = CARD_CHARACTER_SCIENTIST;
	$defs['CARD_CHARACTER_SPIRITUAL_LEADER'] = CARD_CHARACTER_SPIRITUAL_LEADER;
	$defs['CARD_CHARACTER_SPY'] = CARD_CHARACTER_SPY;
	$defs['CARD_CHARACTER_WARRIOR'] = CARD_CHARACTER_WARRIOR;
	$defs['CARD_ARMY'] = CARD_ARMY;
	$defs['CARD_VESSEL'] = CARD_VESSEL;
	$defs['CARD_AIRCRAFT'] = CARD_AIRCRAFT;
	$defs['CARD_EVENT'] = CARD_EVENT;
	$defs['CARD_KNOWLEDGE'] = CARD_KNOWLEDGE;
	$defs['CARD_ORGANIZATION'] = CARD_ORGANIZATION;
	$defs['CARD_TECHNOLOGY'] = CARD_TECHNOLOGY;
	$defs['CARD_LOCATION'] = CARD_LOCATION;
	$defs['CARD_DOCUMENT'] = CARD_DOCUMENT;
	$defs['CARD_RELIC'] = CARD_RELIC;
	$defs['CARD_MATHEMATICIANS'] = CARD_MATHEMATICIANS;
	$defs['SCOPE_ALWAYS_ON'] = SCOPE_ALWAYS_ON;
	$defs['SCOPE_ANYTIME'] = SCOPE_ANYTIME;
	$defs['SCOPE_TURN'] = SCOPE_TURN;
	$defs['SCOPE_ATTACK'] = SCOPE_ATTACK;
	$defs['SCOPE_DEFENSE'] = SCOPE_DEFENSE;
	$defs['APPLY_PLAYER'] = APPLY_PLAYER;
	$defs['APPLY_OPPONENT'] = APPLY_OPPONENT;
	$defs['APPLY_ALL_OPPONENTS'] = APPLY_ALL_OPPONENTS;
	$defs['APPLY_CARD_IN_SAME_COLUMN'] = APPLY_CARD_IN_SAME_COLUMN;
	$defs['APPLY_CARD_PLAYED'] = APPLY_CARD_PLAYED;
	$defs['APPLY_CARDS_IN_COLUMN'] = APPLY_CARDS_IN_COLUMN;
	$defs['APPLY_CARDS_PLAYED'] = APPLY_CARDS_PLAYED;
	$defs['APPLY_GENDER_IN_SAME_COLUMN'] = APPLY_GENDER_IN_SAME_COLUMN;
	$defs['APPLY_RELIGION_IN_SAME_COLUMN'] = APPLY_RELIGION_IN_SAME_COLUMN;
	$defs['APPLY_OPPONENT_CARD_IN_SAME_COLUMN'] = APPLY_OPPONENT_CARD_IN_SAME_COLUMN;
	$defs['APPLY_OPPONENT_CARD_PLAYED'] = APPLY_OPPONENT_CARD_PLAYED;
	$defs['APPLY_OPPONENT_CARDS_IN_COLUMN'] = APPLY_OPPONENT_CARDS_IN_COLUMN;
	$defs['APPLY_OPPONENT_CARDS_PLAYED'] = APPLY_OPPONENT_CARDS_PLAYED;
	$defs['USAGE_IMMEDIATE'] = USAGE_IMMEDIATE;
	$defs['USAGE_HOLD'] = USAGE_HOLD;
	$defs['USAGE_CONSTANT'] = USAGE_CONSTANT;
	$defs['ABILITY_INTERRUPT'] = ABILITY_INTERRUPT;
	$defs['ABILITY_CHOICE'] = ABILITY_CHOICE;
	$defs['ABILITY_MULTI'] = ABILITY_MULTI;
	$defs['RELIGION_CHRISTIAN'] = RELIGION_CHRISTIAN;
	$defs['RELIGION_CATHOLIC'] = RELIGION_CATHOLIC;
	$defs['RELIGION_ORTHODOX_CHRISTIAN'] = RELIGION_ORTHODOX_CHRISTIAN;
	$defs['RELIGION_PROTESTANT'] = RELIGION_PROTESTANT;
	$defs['RELIGION_MUSLIM'] = RELIGION_MUSLIM;
	$defs['RELIGION_MUSLIM_SHIITE'] = RELIGION_MUSLIM_SHIITE;
	$defs['RELIGION_MUSLIM_SUNNI'] = RELIGION_MUSLIM_SUNNI;
	$defs['RELIGION_BUDDHIST'] = RELIGION_BUDDHIST;
	$defs['RELIGION_HINDU'] = RELIGION_HINDU;
	$defs['RELIGION_ATHEIST'] = RELIGION_ATHEIST;
	$defs['RELIGION_JEWISH'] = RELIGION_JEWISH;
	$defs['RELIGION_SHINTO'] = RELIGION_SHINTO;
	$defs['RELIGION_AGNOSTIC'] = RELIGION_AGNOSTIC;
	$defs['RELIGION_PAGAN'] = RELIGION_PAGAN;
	$defs['RELIGION_ZOROASTRIAN'] = RELIGION_ZOROASTRIAN;
	$defs['RELIGION_TAOIST'] = RELIGION_TAOIST;
	$defs['CONTINENT_NORTH_AMERICA'] = CONTINENT_NORTH_AMERICA;
	$defs['CONTINENT_ASIA'] = CONTINENT_ASIA;
	$defs['CONTINENT_EUROPE'] = CONTINENT_EUROPE;
	$defs['CONTINENT_AFRICA'] = CONTINENT_AFRICA;
	$defs['CONTINENT_SOUTH_AMERICA'] = CONTINENT_SOUTH_AMERICA;
	$defs['CONTINENT_ANTARCTICA'] = CONTINENT_ANTARCTICA;
	$defs['CONTINENT_OCEANIA'] = CONTINENT_OCEANIA;
	$defs['CLIMATE_TROPICAL'] = CLIMATE_TROPICAL;
	$defs['CLIMATE_DRY'] = CLIMATE_DRY;
	$defs['CLIMATE_TEMPERATE'] = CLIMATE_TEMPERATE;
	$defs['CLIMATE_CONTINENTAL'] = CLIMATE_CONTINENTAL;
	$defs['CLIMATE_POLAR'] = CLIMATE_POLAR;
	$defs['ETH_WHITE'] = ETH_WHITE;
	$defs['ETH_BLACK'] = ETH_BLACK;
	$defs['ETH_ARAB'] = ETH_ARAB;
	$defs['ETH_HISPANIC'] = ETH_HISPANIC;
	$defs['ETH_ASIAN'] = ETH_ASIAN;
	$defs['ETH_IDIGENOUS'] = ETH_IDIGENOUS;
	$defs['ETH_JEWISH'] = ETH_JEWISH;
	$defs['USAGE_ONCE'] = USAGE_ONCE;

 return $defs;
}
define('MSG_BAD_OPPONENT','That opponent does not exist');
define('MSG_BAD_DECK','That deck does not exist');
define('MSG_BAD_DECK_OWNER','That deck is not yours');
define('MSG_GAME_NOT_FOUND',"Game not found");
define('MSG_GAME_DECLINED',"Game declined");
define('MSG_GAME_OVER',"Game is Over!");
define('MSG_YOU_CANT_DO_THAT',"You can't do that!");
define('MSG_OUT_OF_ATTACKS',"You have no more attacks!");

